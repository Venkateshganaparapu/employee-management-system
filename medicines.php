<?php
// medicines.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle Filters
$where_clauses = [];
$params = [];

if (isset($_GET['filter']) && $_GET['filter'] === 'low_stock') {
    $where_clauses[] = "m.quantity < 50";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clauses[] = "m.category = ?";
    $params[] = $_GET['category'];
}

$query = "SELECT m.*, s.name as supplier_name FROM medicines m LEFT JOIN suppliers s ON m.supplier_id = s.id";
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}
$query .= " ORDER BY m.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

// Fetch suppliers for the dropdown in add/edit modal
$suppliers = $pdo->query("SELECT id, name FROM suppliers WHERE status = 'Active'")->fetchAll();
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0">Medicine Inventory</h2>
            <p class="text-muted small mb-0">Manage all your medicines, stock levels, and physical inventory.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="api/export_csv.php?table=medicines" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i data-lucide="download" width="18"></i> Export CSV
            </a>
            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#medicineModal" onclick="openAddModal()">
                <i data-lucide="plus" width="18"></i> Add Medicine
            </button>
        </div>
    </div>

    <!-- Stats summary row -->
    <div class="row g-3 mb-4" data-aos="fade-up">
        <div class="col-md-4">
            <div class="glass-card stat-card p-3 d-flex align-items-center active" onclick="setQuickFilter('all')" pointer style="cursor: pointer;">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-3"><i data-lucide="layers" width="20"></i></div>
                <div><h5 class="fw-bold mb-0"><?= count($medicines) ?></h5><small class="text-muted">Total Items</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card p-3 d-flex align-items-center" onclick="setQuickFilter('low_stock')" style="cursor: pointer;">
                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle me-3"><i data-lucide="alert-circle" width="20"></i></div>
                <?php $low = count(array_filter($medicines, fn($m) => $m['quantity'] < 50)); ?>
                <div><h5 class="fw-bold mb-0 text-warning"><?= $low ?></h5><small class="text-muted">Low Stock</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card p-3 d-flex align-items-center" onclick="setQuickFilter('expiry')" style="cursor: pointer;">
                <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-circle me-3"><i data-lucide="clock" width="20"></i></div>
                <?php $exp = count(array_filter($medicines, fn($m) => strtotime($m['expiry_date']) < strtotime('+30 days'))); ?>
                <div><h5 class="fw-bold mb-0 text-danger"><?= $exp ?></h5><small class="text-muted">Expiring Soon</small></div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card glass-card border-0" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header bg-transparent border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i data-lucide="search" width="16"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search medicines...">
            </div>
            
            <div class="d-flex gap-2">
                <select id="categoryFilter" class="form-select w-auto glass-effect text-muted small">
                    <option value="">All Categories</option>
                    <?php 
                        $cats = array_unique(array_column($medicines, 'category'));
                        foreach($cats as $c) echo "<option value=\"$c\">$c</option>";
                    ?>
                </select>
            </div>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-modern" id="medicinesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Supplier</th>
                            <th>Expiry Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($medicines as $med): 
                            $is_low = $med['quantity'] < 50;
                            $exp_time = strtotime($med['expiry_date']);
                            $is_expiring = $exp_time < strtotime('+30 days');
                            $is_expired = $exp_time < time();
                        ?>
                        <tr class="medicine-row" 
                            data-name="<?= strtolower($med['name']) ?>" 
                            data-category="<?= strtolower($med['category']) ?>" 
                            data-low="<?= $is_low ? 'true' : 'false' ?>" 
                            data-expiry="<?= $is_expiring ? 'true' : 'false' ?>"
                        >
                            <td class="fw-bold text-muted">#<?= $med['id'] ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($med['name']) ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($med['category']) ?></span></td>
                            <td>
                                <?php if($is_low): ?>
                                    <span class="badge badge-warning"><i data-lucide="alert-triangle" width="12" class="me-1"></i><?= $med['quantity'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= $med['quantity'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold">₹<?= number_format($med['price'], 2) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($med['supplier_name'] ?? 'N/A') ?></td>
                            <td>
                                <?php if($is_expired): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php elseif($is_expiring): ?>
                                    <span class="badge badge-danger">Exp. soon</span>
                                    <div class="small text-danger mt-1"><?= date('M d, Y', strtotime($med['expiry_date'])) ?></div>
                                <?php else: ?>
                                    <span class="text-muted small"><?= date('M d, Y', strtotime($med['expiry_date'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light text-primary rounded-circle" onclick="editMedicine(<?= $med['id'] ?>)" title="Edit">
                                    <i data-lucide="edit-2" width="14"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger rounded-circle ms-1" onclick="deleteMedicine(<?= $med['id'] ?>)" title="Delete">
                                    <i data-lucide="trash-2" width="14"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($medicines)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No medicines found in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Medicine Modal -->
<div class="modal fade" id="medicineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-deep-blue" id="modalTitle">Add Medicine</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="medicineForm">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="medId">
            
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Medicine Name</label>
                <input type="text" class="form-control form-control-modern" name="name" id="medName" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Category</label>
                    <input type="text" class="form-control form-control-modern" name="category" id="medCategory" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Quantity</label>
                    <input type="number" class="form-control form-control-modern" name="quantity" id="medQuantity" required min="0">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Price (₹)</label>
                    <input type="number" step="0.01" class="form-control form-control-modern" name="price" id="medPrice" required min="0">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Expiry Date</label>
                    <input type="date" class="form-control form-control-modern" name="expiry_date" id="medExpiry" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-semibold text-muted">Supplier</label>
                <select class="form-select form-control-modern" name="supplier_id" id="medSupplier" required>
                    <option value="">Select a Supplier</option>
                    <?php foreach($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-custom py-2">Save Medicine</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Modal & Filter Logic
let medicineModal;
const form = document.getElementById('medicineForm');

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Modal
    medicineModal = new bootstrap.Modal(document.getElementById('medicineModal'));
    
    // Initialize Listeners
    const searchInput = document.getElementById('searchInput');
    const globalSearch = document.getElementById('globalSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    
    const triggerFilter = () => applyFilters();
    
    if(searchInput) searchInput.addEventListener('input', triggerFilter);
    if(globalSearch) globalSearch.addEventListener('input', triggerFilter);
    if(categoryFilter) categoryFilter.addEventListener('change', triggerFilter);
});

let activeQuickFilter = 'all';

function setQuickFilter(type) {
    activeQuickFilter = type;
    
    // Update UI
    document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active', 'border-primary'));
    event.currentTarget.classList.add('active', 'border-primary');
    
    applyFilters();
}

function applyFilters() {
    const searchVal = (document.getElementById('searchInput').value || '').toLowerCase();
    const globalVal = (document.getElementById('globalSearch') ? document.getElementById('globalSearch').value : '').toLowerCase();
    const categoryVal = (document.getElementById('categoryFilter').value || '').toLowerCase();
    
    const rows = document.querySelectorAll('.medicine-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const category = row.getAttribute('data-category');
        const isLow = row.getAttribute('data-low') === 'true';
        const isExpiry = row.getAttribute('data-expiry') === 'true';
        
        const matchesSearch = name.includes(searchVal) && name.includes(globalVal);
        const matchesCategory = categoryVal === '' || category === categoryVal;
        
        let matchesQuick = true;
        if (activeQuickFilter === 'low_stock') matchesQuick = isLow;
        if (activeQuickFilter === 'expiry') matchesQuick = isExpiry;
        
        if (matchesSearch && matchesCategory && matchesQuick) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Medicine';
    document.getElementById('formAction').value = 'create';
    form.reset();
}

function editMedicine(id) {
    document.getElementById('modalTitle').innerText = 'Edit Medicine';
    document.getElementById('formAction').value = 'update';
    document.getElementById('medId').value = id;
    
    // Fetch data
    const formData = new FormData();
    formData.append('action', 'fetch');
    formData.append('id', id);
    
    fetch('api/medicine_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                const med = data.data;
                document.getElementById('medName').value = med.name;
                document.getElementById('medCategory').value = med.category;
                document.getElementById('medQuantity').value = med.quantity;
                document.getElementById('medPrice').value = med.price;
                document.getElementById('medExpiry').value = med.expiry_date;
                document.getElementById('medSupplier').value = med.supplier_id;
                medicineModal.show();
            }
        });
}

function deleteMedicine(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('api/medicine_crud.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }
    });
}

// Handle Form Submit
form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    
    fetch('api/medicine_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                medicineModal.hide();
                Swal.fire('Success!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        }).catch(err => {
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
