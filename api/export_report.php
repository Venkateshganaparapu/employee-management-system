<?php
// api/export_report.php
// Generates a CSV download or a printable HTML page for the dashboard report.

require_once '../includes/db.php';

$format = $_GET['format'] ?? 'csv';
$inclStats  = ($_GET['stats']  ?? 1) == 1;
$inclSales  = ($_GET['sales']  ?? 1) == 1;
$inclLow    = ($_GET['low']    ?? 1) == 1;
$inclExpiry = ($_GET['expiry'] ?? 1) == 1;

// ── Fetch Data ────────────────────────────────────────────────────────────────

$stats = [];
if ($inclStats) {
    $stats['total_medicines'] = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
    $stats['total_sales']     = $pdo->query("SELECT IFNULL(SUM(total_amount),0) FROM billing")->fetchColumn();
    $stats['low_stock']       = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity < 50")->fetchColumn();
    $stats['expiring_soon']   = $pdo->query("SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
}

$recentSales = [];
if ($inclSales) {
    $recentSales = $pdo->query("
        SELECT b.id, c.name as customer_name, b.total_amount, b.invoice_date
        FROM billing b
        LEFT JOIN customers c ON b.customer_id = c.id
        ORDER BY b.invoice_date DESC
        LIMIT 20
    ")->fetchAll();
}

$lowMeds = [];
if ($inclLow) {
    $lowMeds = $pdo->query("
        SELECT name, category, quantity, price
        FROM medicines WHERE quantity < 50
        ORDER BY quantity ASC
    ")->fetchAll();
}

$expiryMeds = [];
if ($inclExpiry) {
    $expiryMeds = $pdo->query("
        SELECT name, category, quantity, expiry_date
        FROM medicines
        WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY expiry_date ASC
    ")->fetchAll();
}

$generatedAt = date('Y-m-d H:i:s');

// ── CSV Export ────────────────────────────────────────────────────────────────
if ($format === 'csv') {
    $filename = 'pharmacy_report_' . date('Ymd_His') . '.csv';
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

    fputcsv($out, ['MediCore Pharmacy – Dashboard Report']);
    fputcsv($out, ['Generated:', $generatedAt]);
    fputcsv($out, []);

    if ($inclStats) {
        fputcsv($out, ['── SUMMARY STATISTICS ──']);
        fputcsv($out, ['Metric', 'Value']);
        fputcsv($out, ['Total Medicines', $stats['total_medicines']]);
        fputcsv($out, ['Total Sales (₹)', number_format($stats['total_sales'], 2)]);
        fputcsv($out, ['Low Stock Items', $stats['low_stock']]);
        fputcsv($out, ['Expiring in 30 Days', $stats['expiring_soon']]);
        fputcsv($out, []);
    }

    if ($inclSales && !empty($recentSales)) {
        fputcsv($out, ['── RECENT SALES (Last 20) ──']);
        fputcsv($out, ['Invoice ID', 'Customer', 'Date', 'Amount (₹)']);
        foreach ($recentSales as $s) {
            fputcsv($out, [
                '#INV-' . str_pad($s['id'], 4, '0', STR_PAD_LEFT),
                $s['customer_name'] ?? 'Walk-in Customer',
                $s['invoice_date'],
                number_format($s['total_amount'], 2)
            ]);
        }
        fputcsv($out, []);
    }

    if ($inclLow && !empty($lowMeds)) {
        fputcsv($out, ['── LOW STOCK MEDICINES ──']);
        fputcsv($out, ['Name', 'Category', 'Stock Qty', 'Price (₹)']);
        foreach ($lowMeds as $m) {
            fputcsv($out, [
                $m['name'], $m['category'],
                $m['quantity'], number_format($m['price'], 2)
            ]);
        }
        fputcsv($out, []);
    }

    if ($inclExpiry && !empty($expiryMeds)) {
        fputcsv($out, ['── EXPIRING SOON (30 Days) ──']);
        fputcsv($out, ['Name', 'Category', 'Stock Qty', 'Expiry Date']);
        foreach ($expiryMeds as $m) {
            fputcsv($out, [
                $m['name'], $m['category'],
                $m['quantity'], date('M d, Y', strtotime($m['expiry_date']))
            ]);
        }
    }

    fclose($out);
    exit;
}

// ── Print / PDF Export (HTML) ─────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Report – MediCore</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; background: #fff; padding: 32px; font-size: 13px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #14B8A6; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; color: #1E3A8A; }
        .header p { font-size: 11px; color: #64748b; margin-top: 4px; }
        .logo { font-size: 24px; font-weight: 900; color: #14B8A6; letter-spacing: -1px; }
        .section { margin-bottom: 28px; }
        .section h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; border-left: 4px solid #14B8A6; padding-left: 10px; margin-bottom: 12px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .stat-box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; text-align: center; }
        .stat-box .val { font-size: 22px; font-weight: 700; color: #1E3A8A; }
        .stat-box .lbl { font-size: 11px; color: #64748b; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        .badge-warn { background: #FEF3C7; color: #92400E; border-radius: 20px; padding: 2px 8px; font-size: 11px; }
        .badge-danger { background: #FEE2E2; color: #991B1B; border-radius: 20px; padding: 2px 8px; font-size: 11px; }
        .footer { margin-top: 32px; border-top: 1px solid #e2e8f0; padding-top: 12px; font-size: 11px; color: #94a3b8; display: flex; justify-content: space-between; }
        @media print { body { padding: 16px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="background:#f1f5f9;padding:12px 20px;margin:-32px -32px 24px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:#1E3A8A;">📊 Dashboard Report Preview</span>
        <button onclick="window.print()" style="background:#14B8A6;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:600;">🖨️ Print / Save as PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="logo">💊 MediCore</div>
            <h1>Dashboard Report</h1>
            <p>Generated: <?= $generatedAt ?></p>
        </div>
        <div style="text-align:right;font-size:11px;color:#64748b;">
            <div style="font-weight:600;font-size:14px;color:#1E3A8A;">Pharmacy Management System</div>
            <div>Confidential – Internal Use Only</div>
        </div>
    </div>

    <?php if ($inclStats): ?>
    <div class="section">
        <h2>Summary Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="val"><?= $stats['total_medicines'] ?></div>
                <div class="lbl">Total Medicines</div>
            </div>
            <div class="stat-box">
                <div class="val">₹<?= number_format($stats['total_sales'], 2) ?></div>
                <div class="lbl">Total Sales</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color:#F59E0B;"><?= $stats['low_stock'] ?></div>
                <div class="lbl">Low Stock Items</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color:#EF4444;"><?= $stats['expiring_soon'] ?></div>
                <div class="lbl">Expiring in 30 Days</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($inclSales && !empty($recentSales)): ?>
    <div class="section">
        <h2>Recent Sales (Last 20)</h2>
        <table>
            <thead>
                <tr><th>Invoice ID</th><th>Customer</th><th>Date</th><th>Amount (₹)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentSales as $s): ?>
                <tr>
                    <td><strong>#INV-<?= str_pad($s['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in Customer') ?></td>
                    <td><?= date('M d, Y', strtotime($s['invoice_date'])) ?></td>
                    <td style="font-weight:600;color:#10B981;">₹<?= number_format($s['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($inclLow && !empty($lowMeds)): ?>
    <div class="section">
        <h2>Low Stock Medicines</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Category</th><th>Stock Qty</th><th>Price (₹)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lowMeds as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><span class="badge-warn"><?= htmlspecialchars($m['category']) ?></span></td>
                    <td style="color:#F59E0B;font-weight:600;"><?= $m['quantity'] ?></td>
                    <td>₹<?= number_format($m['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($inclExpiry && !empty($expiryMeds)): ?>
    <div class="section">
        <h2>Expiring Soon (Next 30 Days)</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Category</th><th>Stock Qty</th><th>Expiry Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($expiryMeds as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><?= htmlspecialchars($m['category']) ?></td>
                    <td><?= $m['quantity'] ?></td>
                    <td><span class="badge-danger"><?= date('M d, Y', strtotime($m['expiry_date'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="footer">
        <span>MediCore Pharmacy Management System</span>
        <span>Report generated on <?= $generatedAt ?></span>
    </div>
</body>
</html>
