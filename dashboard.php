<?php
// dashboard.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- Stats ---
$total_medicines = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
$total_sales     = $pdo->query("SELECT IFNULL(SUM(total_amount), 0) FROM billing")->fetchColumn();
$low_stock       = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity < 50")->fetchColumn();
$expiring_soon   = $pdo->query("SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

// --- Recent Sales ---
$recent_sales = $pdo->query("
    SELECT b.id, c.name as customer_name, b.total_amount, b.invoice_date 
    FROM billing b 
    LEFT JOIN customers c ON b.customer_id = c.id 
    ORDER BY b.invoice_date DESC LIMIT 5
")->fetchAll();

// --- Categories for pie chart ---
$cats = $pdo->query("SELECT category, COUNT(*) as count FROM medicines GROUP BY category")->fetchAll();
$cat_labels = [];
$cat_data   = [];
foreach ($cats as $c) {
    $cat_labels[] = $c['category'];
    $cat_data[]   = (int)$c['count'];
}

// --- Monthly sales for current year (default) ---
$currentYear = date('Y');
$monthlySalesStmt = $pdo->prepare("
    SELECT MONTH(invoice_date) as month, IFNULL(SUM(total_amount),0) as total
    FROM billing
    WHERE YEAR(invoice_date) = ?
    GROUP BY MONTH(invoice_date)
    ORDER BY month ASC
");
$monthlySalesStmt->execute([$currentYear]);
$monthlySalesRows = $monthlySalesStmt->fetchAll();

// Build a 12-element array (one per month)
$monthlySalesData = array_fill(0, 12, 0);
foreach ($monthlySalesRows as $row) {
    $monthlySalesData[(int)$row['month'] - 1] = (float)$row['total'];
}

// --- Available years for filter ---
$yearsStmt = $pdo->query("SELECT DISTINCT YEAR(invoice_date) as yr FROM billing ORDER BY yr DESC");
$availableYears = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($availableYears)) {
    $availableYears = [$currentYear];
}
?>

<style>
/* ── Stat cards ── */
.stat-card-link {
    text-decoration: none;
    display: block;
}
.stat-card-link .glass-card {
    cursor: pointer;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}
.stat-card-link:hover .glass-card {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px -8px rgba(0,0,0,0.18);
}
.stat-card-link:hover h3 { color: var(--primary-teal, #14B8A6); }

/* ── Section cards (charts) ── */
.section-card {
    cursor: default;
    transition: box-shadow 0.22s ease;
}
.section-card:hover {
    box-shadow: 0 12px 32px -6px rgba(0,0,0,0.13);
}

/* ── Sales rows ── */
.sale-row {
    cursor: pointer;
    transition: background 0.18s ease;
}
.sale-row:hover td { background: rgba(20,184,166,0.07) !important; }

/* ── Export button ── */
#exportBtn {
    transition: transform 0.18s, box-shadow 0.18s;
}
#exportBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(30,58,138,0.35);
}
#exportBtn:active { transform: scale(0.97); }

/* ── Year select ── */
#yearSelect { cursor: pointer; }

/* ── Category legend items ── */
.cat-legend-item {
    cursor: pointer;
    transition: transform 0.15s, opacity 0.15s;
    border-radius: 8px;
    padding: 3px 6px;
}
.cat-legend-item:hover { transform: scale(1.07); }
.cat-legend-item.dimmed { opacity: 0.35; }

/* ── Hover effect (same as alerts.php) ── */
.hover-effect {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-effect:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ── Loading spinner overlay ── */
#chartOverlay {
    display: none;
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.65);
    border-radius: 16px;
    z-index: 10;
    align-items: center;
    justify-content: center;
}
body.dark-mode #chartOverlay { background: rgba(15,23,42,0.65); }
.chart-wrapper { position: relative; }
</style>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <h2 class="fw-bold text-deep-blue m-0">Dashboard Overview</h2>
        <div class="d-flex gap-2">
            <button id="generateDemoDataBtn" class="btn btn-warning btn-sm d-flex align-items-center gap-2 hover-effect">
                <i data-lucide="database" width="16"></i> Generate Demo Data
            </button>
            <a href="api/export_excel.php" id="exportBtn" class="btn btn-custom btn-sm d-flex align-items-center gap-2 hover-effect">
                <i data-lucide="download" width="16"></i> Export Report
            </a>
        </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="row g-4 mb-4">

        <!-- Total Medicines -->
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <a href="medicines.php" class="stat-card-link" title="View all medicines">
                <div class="glass-card p-4 d-flex align-items-center position-relative overflow-hidden hover-effect">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                        <i data-lucide="pill" width="24" height="24"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 counter" data-target="<?= $total_medicines ?>">0</h3>
                        <p class="text-muted small mb-0 fw-semibold">Total Medicines</p>
                    </div>
                    <div class="position-absolute opacity-25" style="top:-20px;right:-20px;">
                        <i data-lucide="pill" width="120" height="120" color="var(--primary-deep-blue)"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0 m-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary small">View All →</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Sales -->
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <a href="billing.php" class="stat-card-link" title="View all billing / sales">
                <div class="glass-card p-4 d-flex align-items-center position-relative overflow-hidden hover-effect">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                        <i data-lucide="circle-dollar-sign" width="24" height="24"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">₹<span class="counter" data-target="<?= round($total_sales) ?>">0</span></h3>
                        <p class="text-muted small mb-0 fw-semibold">Total Sales</p>
                    </div>
                    <div class="position-absolute opacity-25" style="top:-20px;right:-20px;">
                        <i data-lucide="line-chart" width="120" height="120" color="#10B981"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0 m-2">
                        <span class="badge bg-success bg-opacity-10 text-success small">View All →</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Low Stock -->
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <a href="medicines.php?filter=low_stock" class="stat-card-link" title="View low stock medicines">
                <div class="glass-card p-4 d-flex align-items-center position-relative overflow-hidden hover-effect">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle me-3">
                        <i data-lucide="alert-triangle" width="24" height="24"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 counter" data-target="<?= $low_stock ?>">0</h3>
                        <p class="text-muted small mb-0 fw-semibold">Low Stock</p>
                    </div>
                    <div class="position-absolute opacity-25" style="top:-20px;right:-20px;">
                        <i data-lucide="boxes" width="120" height="120" color="#F59E0B"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0 m-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning small">Restock →</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Expiring Soon -->
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <a href="alerts.php" class="stat-card-link" title="View expiry alerts">
                <div class="glass-card p-4 d-flex align-items-center position-relative overflow-hidden hover-effect">
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle me-3">
                        <i data-lucide="clock" width="24" height="24"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 counter" data-target="<?= $expiring_soon ?>">0</h3>
                        <p class="text-muted small mb-0 fw-semibold">Expiring Soon</p>
                    </div>
                    <div class="position-absolute opacity-25" style="top:-20px;right:-20px;">
                        <i data-lucide="calendar-off" width="120" height="120" color="#EF4444"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0 m-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger small">View Alerts →</span>
                    </div>
                </div>
            </a>
        </div>

    </div><!-- /stat cards -->

    <!-- ── CHARTS ── -->
    <div class="row g-4 mb-4">

        <!-- Sales Overview chart -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="card glass-card section-card h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i data-lucide="bar-chart-2" width="18" class="me-1 text-teal"></i>
                        Sales Overview
                    </h5>
                    <select id="yearSelect" class="form-select form-select-sm w-auto glass-effect">
                        <?php foreach ($availableYears as $yr): ?>
                            <option value="<?= $yr ?>" <?= $yr == $currentYear ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                        <?php if (!in_array($currentYear, $availableYears)): ?>
                            <option value="<?= $currentYear ?>" selected><?= $currentYear ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="card-body chart-wrapper">
                    <div id="chartOverlay">
                        <div class="spinner-border text-teal" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                    <canvas id="salesChart" height="100"></canvas>
                    <p id="salesNoData" class="text-center text-muted py-4" style="display:none!important;">
                        <i data-lucide="bar-chart-2" width="40" class="opacity-25 d-block mx-auto mb-2"></i>
                        No sales data for this year.
                    </p>
                </div>
            </div>
        </div>

        <!-- Category doughnut chart -->
        <div class="col-lg-4" data-aos="fade-left">
            <div class="card glass-card section-card h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i data-lucide="pie-chart" width="18" class="me-1 text-teal"></i>
                        Medicine Categories
                    </h5>
                    <a href="medicines.php" class="btn btn-outline-secondary btn-sm hover-effect" style="font-size:11px;">
                        <i data-lucide="external-link" width="12"></i> View
                    </a>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center flex-column">
                    <div style="height:230px;width:100%;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <!-- Custom clickable legend -->
                    <div id="catLegend" class="d-flex flex-wrap justify-content-center gap-2 mt-2"></div>
                </div>
            </div>
        </div>

    </div><!-- /charts -->

    <!-- ── RECENT SALES TABLE ── -->
    <div class="row" data-aos="fade-up">
        <div class="col-12">
            <div class="card glass-card section-card border-0 pb-3">
                <div class="card-header bg-transparent border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark m-0">
                        <i data-lucide="clock" width="18" class="me-1 text-teal"></i>
                        Recent Sales Activity
                    </h5>
                    <a href="billing.php" class="text-teal text-decoration-none small fw-semibold hover-effect">
                        View All <i data-lucide="arrow-right" width="14"></i>
                    </a>
                </div>
                <div class="table-responsive px-3">
                    <table class="table table-modern table-hover">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Customer Name</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr class="sale-row" onclick="window.location='billing.php?invoice=<?= $sale['id'] ?>'" title="View invoice #<?= $sale['id'] ?>">
                                <td class="fw-bold text-deep-blue">#INV-<?= str_pad($sale['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></td>
                                <td class="text-muted"><i data-lucide="calendar" width="14" class="me-1"></i><?= date('M d, Y', strtotime($sale['invoice_date'])) ?></td>
                                <td class="fw-bold text-success">₹<?= number_format($sale['total_amount'], 2) ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td class="text-end">
                                    <a href="billing.php?invoice=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-primary hover-effect" onclick="event.stopPropagation()">
                                        <i data-lucide="eye" width="13"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_sales)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i data-lucide="inbox" width="40" class="opacity-25 d-block mx-auto mb-2"></i>
                                    No sales recorded yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div><!-- /container-fluid -->

<!-- ── EXPORT MODAL ── -->
<!-- ── EXPORT MODAL (Data Analysis Center) ── -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius:24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-deep-blue" id="exportModalLabel">
                    <i data-lucide="bar-chart-3" width="24" class="me-2 text-teal"></i>Data Analysis Export Center
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 px-4">
                <p class="text-muted small">Choose what to include in your executive summary report.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Report Format</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="exportFmt" id="fmtCSV" value="csv" checked>
                        <label class="btn btn-outline-secondary btn-sm hover-effect" for="fmtCSV">CSV</label>
                        <input type="radio" class="btn-check" name="exportFmt" id="fmtPrint" value="print">
                        <label class="btn btn-outline-secondary btn-sm hover-effect" for="fmtPrint">Print/PDF</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Sections to Include</label>
                    <div class="d-flex flex-column gap-1">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="inclStats" checked><label class="form-check-label small" for="inclStats">Statistics Overview</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="inclSales" checked><label class="form-check-label small" for="inclSales">Recent Sales History</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="inclLow" checked><label class="form-check-label small" for="inclLow">Inventory Alerts</label></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button id="doExportBtn" class="btn btn-custom btn-sm hover-effect">Download Summary</button>
            </div>
        </div>
    </div>
</div>

<!-- ── SCRIPTS ── -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    const generateDemoBtn = document.getElementById('generateDemoDataBtn');
    if (generateDemoBtn) {
        generateDemoBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to generate massive demo data? This might take a few moments.')) {
                generateDemoBtn.disabled = true;
                generateDemoBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
                
                fetch('api/generate_demo_data.php')
                    .then(res => {
                        if (!res.ok) throw new Error('Server returned ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Generation failed:', err);
                        Swal.fire('Error', 'Generation failed: ' + err.message, 'error');
                        generateDemoBtn.disabled = false;
                        generateDemoBtn.innerHTML = '<i data-lucide="database" width="16"></i> Generate Demo Data';
                        lucide.createIcons();
                    });
            }
        });
    }

    /* ── 1. Counter animation ── */
    document.querySelectorAll('.counter').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const speed  = 200;
        const run = () => {
            const cur = +counter.innerText.replace(/,/g,'');
            const inc = Math.ceil(target / speed);
            if (cur < target) {
                counter.innerText = Math.min(cur + inc, target);
                setTimeout(run, 12);
            } else {
                counter.innerText = target;
            }
        };
        run();
    });

    /* ── 2. Palette ── */
    const PALETTE = [
        '#14B8A6','#1E3A8A','#8B5CF6','#F59E0B','#EF4444',
        '#10B981','#F97316','#06B6D4','#EC4899','#84CC16',
        '#6366F1','#D97706','#BE185D','#0891B2','#65A30D'
    ];

    /* ── 3. Sales Line Chart ── */
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    let gradient = salesCtx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(20,184,166,0.55)');
    gradient.addColorStop(1, 'rgba(20,184,166,0.02)');

    const initialData = <?= json_encode(array_values($monthlySalesData)) ?>;
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales (₹)',
                data: initialData,
                backgroundColor: gradient,
                borderColor: '#14B8A6',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1E3A8A',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₹' + ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2})
                    }
                }
            },
            scales: {
                y: {
                    grid: { borderDash: [5,5], color: '#e2e8f0' },
                    ticks: { callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) }
                },
                x: { grid: { display: false } }
            }
        }
    });

    /* Year filter – fetch real data via API */
    const overlay     = document.getElementById('chartOverlay');
    const salesNoData = document.getElementById('salesNoData');
    document.getElementById('yearSelect').addEventListener('change', function () {
        const year = this.value;
        overlay.style.display = 'flex';
        fetch(`api/sales_by_year.php?year=${year}`)
            .then(r => r.json())
            .then(data => {
                salesChart.data.datasets[0].data = data;
                salesChart.update('active');
                const hasData = data.some(v => v > 0);
                salesNoData.style.display = hasData ? 'none' : 'block';
                salesCtx.canvas.style.display = hasData ? '' : 'none';
            })
            .catch(() => {})
            .finally(() => { overlay.style.display = 'none'; });
    });

    /* ── 4. Category Doughnut Chart ── */
    const catLabels = <?= json_encode($cat_labels) ?>;
    const catData   = <?= json_encode($cat_data) ?>;
    const catColors = catLabels.map((_, i) => PALETTE[i % PALETTE.length]);

    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catColors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} medicines`
                    }
                }
            },
            cutout: '70%',
            onClick: (evt, elements) => {
                if (elements.length) {
                    const cat = catLabels[elements[0].index];
                    window.location.href = `medicines.php?category=${encodeURIComponent(cat)}`;
                }
            }
        }
    });

    /* Custom clickable legend */
    const legendEl = document.getElementById('catLegend');
    catLabels.forEach((label, i) => {
        const item = document.createElement('span');
        item.className = 'cat-legend-item d-flex align-items-center gap-1 small fw-semibold';
        item.title = `Click to filter by ${label}`;
        item.innerHTML = `<span style="width:10px;height:10px;border-radius:50%;background:${catColors[i]};display:inline-block;"></span>${label}`;
        item.addEventListener('click', () => {
            window.location.href = `medicines.php?category=${encodeURIComponent(label)}`;
        });
        legendEl.appendChild(item);
    });

    /* ── 5. Export Center (Legacy Support) ── */
    const exportModal  = new bootstrap.Modal(document.getElementById('exportModal'));

    const doExportBtn = document.getElementById('doExportBtn');
    if (doExportBtn) {
        doExportBtn.addEventListener('click', () => {
            const fmtEl  = document.querySelector('input[name="exportFmt"]:checked');
            const fmt    = fmtEl ? fmtEl.value : 'csv';
            const stats  = document.getElementById('inclStats').checked  ? 1 : 0;
            const sales  = document.getElementById('inclSales').checked  ? 1 : 0;
            const low    = document.getElementById('inclLow').checked    ? 1 : 0;
            const expiry = (document.getElementById('inclExpiry') && document.getElementById('inclExpiry').checked) ? 1 : 0;

            if (fmt === 'print') {
                window.open(`api/export_report.php?format=print&stats=${stats}&sales=${sales}&low=${low}&expiry=${expiry}`, '_blank');
            } else {
                window.location.href = `api/export_report.php?format=csv&stats=${stats}&sales=${sales}&low=${low}&expiry=${expiry}`;
            }
            exportModal.hide();
        });
    }

    /* ── 6. Global Search Implementation ── */
    const globalSearchInput = document.getElementById('globalSearch');
    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.sale-row');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

});
</script>

<?php require_once 'includes/footer.php'; ?>
