<?php
// suppliers.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Custom Profile Cards */
.profile-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition-normal);
    border: 1px solid rgba(139, 92, 246, 0.05); /* Accent Purple base */
}
.profile-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(139, 92, 246, 0.4);
}
body.dark-mode .profile-card { background: #1E293B; border-color: #334155; }
body.dark-mode .profile-card:hover { border-color: var(--accent-purple); }

.company-logo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: rgba(139, 92, 246, 0.1);
    color: var(--accent-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    font-weight: 600;
}
.status-indicator::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: var(--primary-teal);
    animation: pulse 2s infinite;
}
.status-indicator.inactive::before {
    background-color: #EF4444;
    animation: none;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(20, 184, 166, 0.4); }
    70% { box-shadow: 0 0 0 5px rgba(20, 184, 166, 0); }
    100% { box-shadow: 0 0 0 0 rgba(20, 184, 166, 0); }
}

.table-layout { display: none; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0">Verified Suppliers</h2>
            <p class="text-muted small mb-0">Manage pharmaceutical vendors and wholesale distributors.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="api/export_csv.php?table=suppliers" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i data-lucide="download" width="18"></i> Export CSV
            </a>
            <button class="btn btn-custom px-4" style="background: linear-gradient(135deg, #8B5CF6 0%, #1E3A8A 100%);" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="openAddModal()">
                <i data-lucide="truck" width="18" class="me-2"></i> Add Supplier
            </button>
        </div>
    </div>

    <!-- Layout Toggle -->
    <div class="d-flex justify-content-end mb-4 border-bottom pb-3" data-aos="fade-up">
        <div class="d-flex gap-2">
            <button class="btn btn-outline-custom active border-purple text-purple"><i data-lucide="grid-3x3" width="16"></i></button>
            <button class="btn btn-light border text-muted"><i data-lucide="list" width="16"></i></button>
        </div>
    </div>

    <!-- Cards Layout -->
    <div class="row g-4" id="suppliersContainer" data-aos="fade-up" data-aos-delay="100">
        <?php foreach($suppliers as $s): 
            $isActive = $s['status'] === 'Active';    
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="profile-card position-relative h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="company-logo-placeholder">
                        <i data-lucide="building-2" width="28"></i>
                    </div>
                    <!-- Status dot -->
                    <div class="status-indicator <?= !$isActive ? 'alert-danger inactive text-danger' : 'text-teal' ?> px-2 py-1 rounded bg-light">
                        <?= $s['status'] ?>
                    </div>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($s['name']) ?></h5>
                <p class="text-muted small mb-3"><i data-lucide="user" width="14" class="me-1"></i> <?= htmlspecialchars($s['contact_person']) ?></p>
                
                <div class="bg-light rounded p-3 mb-3 flex-grow-1">
                    <div class="d-flex align-items-center mb-2 text-dark small">
                        <i data-lucide="phone" width="16" class="text-muted me-2"></i> <?= htmlspecialchars($s['phone']) ?>
                    </div>
                    <div class="d-flex align-items-center text-dark small">
                        <i data-lucide="mail" width="16" class="text-muted me-2"></i> <?= htmlspecialchars($s['email']) ?>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-auto">
                    <button class="btn btn-sm btn-outline-custom flex-grow-1 border-purple text-purple" onclick="editSupplier(<?= $s['id'] ?>)">Edit Profile</button>
                    <button class="btn btn-sm btn-light border text-muted px-3" onclick="toggleStatus(<?= $s['id'] ?>)" title="Toggle Status"><i data-lucide="power" width="16"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-deep-blue" id="modalTitle">Add Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="supplierForm">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="supId">
            
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Company Name</label>
                <input type="text" class="form-control form-control-modern" name="name" id="supName" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Contact Person</label>
                <input type="text" class="form-control form-control-modern" name="contact_person" id="supContact">
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Phone Number</label>
                    <input type="text" class="form-control form-control-modern" name="phone" id="supPhone">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Email Address</label>
                    <input type="email" class="form-control form-control-modern" name="email" id="supEmail">
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-custom py-2" style="background: linear-gradient(135deg, #8B5CF6 0%, #1E3A8A 100%); border: none;">Save Supplier</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
let supplierModal;
const form = document.getElementById('supplierForm');

document.addEventListener('DOMContentLoaded', () => {
    supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
});

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Supplier';
    document.getElementById('formAction').value = 'create';
    form.reset();
}

function editSupplier(id) {
    document.getElementById('modalTitle').innerText = 'Edit Supplier';
    document.getElementById('formAction').value = 'update';
    document.getElementById('supId').value = id;
    
    const formData = new FormData();
    formData.append('action', 'fetch');
    formData.append('id', id);
    
    fetch('api/supplier_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('supName').value = data.data.name;
                document.getElementById('supContact').value = data.data.contact_person || '';
                document.getElementById('supPhone').value = data.data.phone || '';
                document.getElementById('supEmail').value = data.data.email || '';
                supplierModal.show();
            }
        });
}

function toggleStatus(id) {
    Swal.fire({
        title: 'Change Status?',
        text: "This will toggle the supplier's active status.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#8B5CF6',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            fetch('api/supplier_crud.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire('Updated!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }
    });
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    fetch('api/supplier_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                supplierModal.hide();
                Swal.fire('Success!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
