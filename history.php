<?php
// history.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Fetch all billing records with aggregated medicine names
$stmt = $pdo->query("
    SELECT b.id, b.total_amount, b.invoice_date, c.name as customer_name,
    GROUP_CONCAT(m.name SEPARATOR ', ') as medicine_list
    FROM billing b 
    LEFT JOIN customers c ON b.customer_id = c.id 
    LEFT JOIN billing_items bi ON b.id = bi.billing_id
    LEFT JOIN medicines m ON bi.medicine_id = m.id
    GROUP BY b.id
    ORDER BY b.invoice_date DESC
");
$billings = $stmt->fetchAll();

// Stats calculation
$total_revenue = array_sum(array_column($billings, 'total_amount'));
$total_orders  = count($billings);
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0">Sales History</h2>
            <p class="text-muted small mb-0">Track all past transactions and medicine sales details.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="api/export_csv.php?table=sales" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i data-lucide="download" width="18"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4" data-aos="fade-up">
        <div class="col-md-6">
            <div class="glass-card p-4 d-flex align-items-center border-0">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                    <i data-lucide="banknote" width="28"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-success">₹<?= number_format($total_revenue, 2) ?></h3>
                    <small class="text-muted fw-semibold">Lifetime Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card p-4 d-flex align-items-center border-0">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                    <i data-lucide="shopping-cart" width="28"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-primary"><?= $total_orders ?></h3>
                    <small class="text-muted fw-semibold">Total Invoices</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card glass-card border-0" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header bg-transparent border-0 pt-4 pb-2">
            <div class="input-group" style="max-width: 350px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i data-lucide="search" width="16"></i></span>
                <input type="text" id="historySearch" class="form-control border-start-0 ps-0" placeholder="Search by Invoice ID or Customer Name...">
            </div>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-modern" id="historyTable">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Customer Name</th>
                            <th>Medicines</th>
                            <th>Date & Time</th>
                            <th>Total Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($billings as $bill): ?>
                        <tr class="history-row">
                            <td class="fw-bold text-deep-blue">#INV-<?= str_pad($bill['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($bill['customer_name'] ?? 'Walk-in Customer') ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($bill['medicine_list'] ?? 'N/A') ?></td>
                            <td class="text-muted small"><i data-lucide="calendar" width="14" class="me-1"></i><?= date('M d, Y - h:i A', strtotime($bill['invoice_date'])) ?></td>
                            <td class="fw-bold text-success">₹<?= number_format($bill['total_amount'], 2) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="viewOrderDetails(<?= $bill['id'] ?>)">
                                    <i data-lucide="eye" width="14" class="me-1"></i> View Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($billings)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No sales history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold text-deep-blue" id="orderIdText">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div id="modalLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Loading details...</p>
        </div>
        
        <div id="modalContent" style="display: none;">
            <div class="row mb-4">
                <div class="col-md-6">
                    <small class="text-muted d-block fw-semibold mb-1">Customer</small>
                    <p class="fw-bold text-dark m-0 h5" id="detailCustomerName"></p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <small class="text-muted d-block fw-semibold mb-1">Transaction Date</small>
                    <p class="text-muted m-0" id="detailDate"></p>
                </div>
            </div>

            <table class="table table-sm text-dark mt-2">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-3">Medicine Name</th>
                        <th class="text-center">Rate</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end pe-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailItemsBody"></tbody>
            </table>
            
            <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-primary bg-opacity-10 rounded-3">
                <span class="fw-bold text-primary">Final Amount Paid:</span>
                <span class="fw-bold text-primary h3 m-0" id="detailTotalAmount"></span>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Modal Handling
let orderModal;

// Unified Filter Helper
const applyHistoryFilters = () => {
    const historySearch = (document.getElementById('historySearch').value || '').toLowerCase();
    const globalSearch = (document.getElementById('globalSearch') ? document.getElementById('globalSearch').value : '').toLowerCase();
    
    let rows = document.querySelectorAll('.history-row');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = (text.includes(historySearch) && text.includes(globalSearch)) ? '' : 'none';
    });
};

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Modal
    orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    
    // Listeners
    const searchInput = document.getElementById('historySearch');
    const globalSearchInput = document.getElementById('globalSearch');
    
    if(searchInput) searchInput.addEventListener('input', applyHistoryFilters);
    if(globalSearchInput) globalSearchInput.addEventListener('input', applyHistoryFilters);
    
    // Refresh icons
    lucide.createIcons();
});

function viewOrderDetails(id) {
    document.getElementById('orderIdText').innerText = `Order #INV-${id.toString().padStart(5, '0')}`;
    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('modalContent').style.display = 'none';
    orderModal.show();

    fetch(`api/history_api.php?action=order_details&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const summary = data.summary;
                document.getElementById('detailCustomerName').innerText = summary.customer_name || 'Walk-in Customer';
                document.getElementById('detailDate').innerText = new Date(summary.invoice_date).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
                document.getElementById('detailTotalAmount').innerText = '₹' + parseFloat(summary.total_amount).toFixed(2);
                
                const itemsBody = document.getElementById('detailItemsBody');
                itemsBody.innerHTML = '';
                data.items.forEach(item => {
                    itemsBody.innerHTML += `
                        <tr>
                            <td class="ps-3 fw-semibold">${item.medicine_name}</td>
                            <td class="text-center">₹${parseFloat(item.price).toFixed(2)}</td>
                            <td class="text-center">${item.quantity}</td>
                            <td class="text-end pe-3 fw-bold">₹${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                document.getElementById('modalLoading').style.display = 'none';
                document.getElementById('modalContent').style.display = 'block';
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Could not load order details.', 'error');
            orderModal.hide();
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // Refresh icons
    lucide.createIcons();
});
</script>

<?php require_once 'includes/footer.php'; ?>
