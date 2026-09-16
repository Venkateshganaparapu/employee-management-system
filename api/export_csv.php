<?php
// api/export_csv.php
// BUG FIX: Corrected column names to match actual database schema.
// Added session authentication and table whitelist.

require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();

// Whitelist allowed tables
$allowed = ['medicines', 'sales', 'customers', 'suppliers'];
$table   = $_GET['table'] ?? '';

if (!in_array($table, $allowed, true)) {
    http_response_code(400);
    die('Invalid table parameter.');
}

$filename = 'export_' . $table . '_' . date('Ymd_His') . '.csv';

header('Content-Description: File Transfer');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

try {
    switch ($table) {
        case 'sales':
            // Matches actual schema: billing + billing_items + medicines
            fputcsv($out, ['Invoice_ID', 'Date', 'Customer_Name', 'Medicine_Name', 'Quantity', 'Unit_Price', 'Subtotal', 'Invoice_Total']);
            $rows = $pdo->query("
                SELECT b.id AS Invoice_ID,
                       DATE(b.invoice_date) AS Date,
                       COALESCE(c.name, 'Walk-in Customer') AS Customer_Name,
                       COALESCE(m.name, 'Unknown') AS Medicine_Name,
                       bi.quantity AS Quantity,
                       m.price AS Unit_Price,
                       bi.subtotal AS Subtotal,
                       b.total_amount AS Invoice_Total
                FROM billing b
                LEFT JOIN customers c ON b.customer_id = c.id
                LEFT JOIN billing_items bi ON b.id = bi.billing_id
                LEFT JOIN medicines m ON bi.medicine_id = m.id
                ORDER BY b.id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            break;

        case 'medicines':
            // Matches actual schema: medicines table columns
            fputcsv($out, ['Medicine_ID', 'Name', 'Category', 'Quantity', 'Price', 'Supplier', 'Expiry_Date']);
            $rows = $pdo->query("
                SELECT m.id, m.name, m.category, m.quantity, m.price,
                       COALESCE(s.name, 'N/A') AS supplier_name,
                       m.expiry_date
                FROM medicines m
                LEFT JOIN suppliers s ON m.supplier_id = s.id
                ORDER BY m.id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            break;

        case 'customers':
            // Matches actual schema: id, name, phone, email, history_notes
            fputcsv($out, ['Customer_ID', 'Name', 'Phone', 'Email', 'History_Notes']);
            $rows = $pdo->query("
                SELECT id, name, phone, email, history_notes
                FROM customers ORDER BY id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            break;

        case 'suppliers':
            // Matches actual schema: id, name, contact_person, phone, email, status
            fputcsv($out, ['Supplier_ID', 'Name', 'Contact_Person', 'Phone', 'Email', 'Status']);
            $rows = $pdo->query("
                SELECT id, name, contact_person, phone, email, status
                FROM suppliers ORDER BY id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            break;
    }
} catch (PDOException $e) {
    error_log('[MediCore][export_csv] ' . $e->getMessage());
    fputcsv($out, ['Error', 'Could not retrieve data. Please try again.']);
}

fclose($out);
exit;
