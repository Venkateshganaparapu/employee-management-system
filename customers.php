<?php
// customers.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
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
    border: 1px solid rgba(20, 184, 166, 0.05);
    height: 100%;
}
.profile-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(20, 184, 166, 0.4);
}
body.dark-mode .profile-card { background: #1E293B; border-color: #334155; }
body.dark-mode .profile-card:hover { border-color: var(--primary-teal); }

.profile-img-placeholder {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--bg-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0 auto 15px;
    box-shadow: var(--shadow-md);
}

.contact-info {
    font-size: 0.9rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 8px;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0">Customer Directory</h2>
            <p class="text-muted small mb-0">View all registered customers and their history records.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="api/export_csv.php?table=customers" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i data-lucide="download" width="18"></i> Export CSV
            </a>
            <button class="btn btn-custom px-4" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="openAddModal()"><i data-lucide="user-plus" width="18" class="me-2"></i> Add Customer</button>
        </div>
    </div>

    <!-- Active Filter row -->
    <div class="d-flex justify-content-between mb-4 border-bottom pb-3" data-aos="fade-up">
        <div class="input-group" style="max-width: 350px;">
            <span class="input-group-text bg-white border-end-0 text-muted"><i data-lucide="search" width="16"></i></span>
            <input type="text" id="custSearch" class="form-control form-control-modern border-start-0 ps-0" placeholder="Search customers...">
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-custom active"><i data-lucide="grid-3x3" width="16"></i></button>
            <button class="btn btn-light border text-muted"><i data-lucide="list" width="16"></i></button>
        </div>
    </div>

    <!-- Cards Layout -->
    <div class="row g-4" id="customersContainer" data-aos="fade-up" data-aos-delay="100">
        <?php foreach($customers as $c): ?>
        <div class="col-md-6 col-lg-4 col-xl-3 cust-card" data-name="<?= strtolower($c['name']) ?>">
            <div class="profile-card text-center position-relative">
                <span class="position-absolute top-0 end-0 mt-3 me-3 badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active</span>
                
                <div class="profile-img-placeholder">
                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($c['name']) ?></h5>
                <span class="badge bg-light text-muted border mb-3">ID: #CUS-<?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></span>
                
                <div class="contact-info">
                    <i data-lucide="phone" width="16"></i>
                    <?= htmlspecialchars($c['phone'] ?? 'No Phone') ?>
                </div>
                <div class="contact-info">
                    <i data-lucide="mail" width="16"></i>
                    <?= htmlspecialchars($c['email'] ?? 'No Email') ?>
                </div>
                
                <div class="mt-4 pt-3 border-top text-start">
                    <small class="text-muted fw-semibold">History Notes:</small>
                    <p class="text-dark small mt-1 mb-0 opacity-75 text-truncate">
                        <?= htmlspecialchars($c['history_notes'] ?: 'No historical clinical notes available.') ?>
                    </p>
                </div>
                
                <div class="mt-4 d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-light text-primary rounded-circle" onclick="editCustomer(<?= $c['id'] ?>)"><i data-lucide="edit" width="14"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-deep-blue" id="modalTitle">Add Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="customerForm">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="custId">
            
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Full Name</label>
                <input type="text" class="form-control form-control-modern" name="name" id="custName" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Phone Number</label>
                    <input type="text" class="form-control form-control-modern" name="phone" id="custPhone">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold text-muted">Email Address</label>
                    <input type="email" class="form-control form-control-modern" name="email" id="custEmail">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-semibold text-muted">History Notes</label>
                <textarea class="form-control form-control-modern" name="history_notes" id="custNotes" rows="3" placeholder="Clinical history, regular meds..."></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-custom py-2">Save Customer</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('custSearch').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll('.cust-card');
    cards.forEach(card => {
        let name = card.getAttribute('data-name');
        card.style.display = name.includes(filter) ? 'block' : 'none';
    });
});

let customerModal;
const form = document.getElementById('customerForm');

document.addEventListener('DOMContentLoaded', () => {
    customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
});

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Customer';
    document.getElementById('formAction').value = 'create';
    form.reset();
}

function editCustomer(id) {
    document.getElementById('modalTitle').innerText = 'Edit Customer';
    document.getElementById('formAction').value = 'update';
    document.getElementById('custId').value = id;
    
    const formData = new FormData();
    formData.append('action', 'fetch');
    formData.append('id', id);
    
    fetch('api/customer_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('custName').value = data.data.name;
                document.getElementById('custPhone').value = data.data.phone || '';
                document.getElementById('custEmail').value = data.data.email || '';
                document.getElementById('custNotes').value = data.data.history_notes || '';
                customerModal.show();
            }
        });
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    fetch('api/customer_crud.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                customerModal.hide();
                Swal.fire('Success!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
