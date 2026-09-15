<?php
// billing.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Fetch customers for dropdown
$customers = $pdo->query("SELECT id, name FROM customers")->fetchAll();
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Specific POS Styles */
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    max-height: 250px;
    overflow-y: auto;
    display: none;
}

.search-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: var(--transition-fast);
}

.search-item:hover {
    background: #f8fafc;
    color: var(--primary-teal);
}

.cart-item {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-sm);
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.qty-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #cbd5e1;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition-fast);
}
.qty-btn:hover { background: var(--bg-body); }

/* Invoice Preview */
.invoice-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: var(--shadow-md);
    animation: fadeInRight 0.6s ease-out;
}

/* Print Styles */
@media print {
    body * { visibility: hidden; }
    .invoice-card, .invoice-card * { visibility: visible; }
    .invoice-card { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
    .no-print { display: none !important; }
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0">Point of Sale</h2>
            <p class="text-muted small mb-0">Search medicines, add to cart, and generate invoice.</p>
        </div>
        <a href="history.php" class="btn btn-outline-primary rounded-pill px-4">
            <i data-lucide="history" width="18" class="me-2"></i> View History
        </a>
    </div>

    <div class="row g-4 h-100">
        <!-- Left: Search & Cart -->
        <div class="col-lg-7" data-aos="fade-right">
            <div class="glass-card p-4 h-100 border-0">
                <!-- Search Bar -->
                <div class="position-relative mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted py-3">
                            <i data-lucide="search" width="20"></i>
                        </span>
                        <input type="text" id="medSearch" class="form-control form-control-modern border-start-0 ps-0 py-3 text-lg" placeholder="Search medicine by name..." autocomplete="off">
                    </div>
                    <div id="searchResults" class="search-results text-dark"></div>
                </div>

                <!-- Cart Header -->
                <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-3">
                    <h5 class="fw-bold text-dark m-0"><i data-lucide="shopping-cart" class="me-2 text-primary" width="20"></i> Current Checkout</h5>
                    <button class="btn btn-sm text-danger fw-semibold bg-danger bg-opacity-10 rounded-pill px-3" onclick="clearCart()">Clear All</button>
                </div>

                <!-- Cart Items Container -->
                <div id="cartContainer" class="pe-2 text-dark" style="max-height: 400px; overflow-y: auto;">
                    <!-- Items rendered here -->
                </div>

                <div class="text-center py-5 text-muted" id="emptyCartMsg">
                    <i data-lucide="shopping-bag" width="48" class="mb-3 opacity-50"></i>
                    <p>Your cart is empty.<br>Search and add medicines to begin.</p>
                </div>

                <div class="mt-4 border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fw-semibold">Subtotal:</span>
                        <span class="fw-bold text-dark" id="cartSubtotal">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fw-semibold">Tax (0%):</span>
                        <span class="fw-bold text-dark">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary bg-opacity-10 rounded-3">
                        <span class="fw-bold text-primary" style="font-size: 1.2rem;">Total:</span>
                        <span class="fw-bold text-primary" style="font-size: 1.5rem;" id="cartTotal">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Invoice Preview -->
        <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
            <div class="invoice-card h-100 d-flex flex-column">
                
                <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-gradient-custom text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i data-lucide="crosshair"></i>
                        </div>
                        <h4 class="fw-bold text-deep-blue m-0">MediCore</h4>
                    </div>
                    <div class="text-end">
                        <h5 class="fw-bold mb-1">INVOICE</h5>
                        <small class="text-muted" id="invoiceDate"><?= date('M d, Y') ?></small>
                    </div>
                </div>

                <div class="mb-4 text-dark">
                    <label class="form-label small fw-semibold text-muted no-print">Select Customer (Optional)</label>
                    <select id="customerSelect" class="form-select form-select-sm glass-effect mb-2 no-print">
                        <option value="">Walk-in Customer</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="d-none" id="printCustomerInfo">
                        <p class="mb-0 fw-semibold text-dark">Bill To:</p>
                        <p class="mb-0 text-muted" id="printCustomerName">Walk-in Customer</p>
                    </div>
                </div>

                <table class="table table-sm text-dark mt-2" id="invoiceTable">
                    <thead>
                        <tr class="text-muted small">
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody">
                        <!-- Invoice items via JS -->
                    </tbody>
                </table>

                <div class="mt-auto pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-muted">Grand Total</span>
                        <h3 class="fw-bold text-deep-blue m-0" id="invoiceTotal">₹0.00</h3>
                    </div>

                    <div class="d-grid gap-2 no-print">
                        <button class="btn btn-custom py-2 w-100 shadow" id="btnCheckout" onclick="processCheckout()">
                            <i data-lucide="check-circle" width="18" class="me-2"></i> Confirm & Generate
                        </button>
                        <button class="btn btn-outline-custom py-2 w-100" id="btnPrint" onclick="window.print()" disabled>
                            <i data-lucide="printer" width="18" class="me-2"></i> Print Invoice
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

// Search functionality
const searchInput = document.getElementById('medSearch');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length === 0) {
        searchResults.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'search');
        formData.append('query', query);
        
        fetch('api/billing_api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                searchResults.innerHTML = '';
                if(data.status === 'success' && data.data.length > 0) {
                    data.data.forEach(med => {
                        searchResults.innerHTML += `
                            <div class="search-item d-flex justify-content-between align-items-center" onclick="addToCart(${med.id}, '${med.name}', ${med.price}, ${med.quantity})">
                                <div>
                                    <span class="fw-semibold">${med.name}</span>
                                    <small class="text-muted d-block">Stock: ${med.quantity}</small>
                                </div>
                                <span class="fw-bold text-teal">₹${parseFloat(med.price).toFixed(2)}</span>
                            </div>
                        `;
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-muted">No medicines found or out of stock.</div>';
                    searchResults.style.display = 'block';
                }
            });
    }, 300);
});

// Hide search on click outside
document.addEventListener('click', function(e) {
    if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function addToCart(id, name, price, maxQty) {
    searchResults.style.display = 'none';
    searchInput.value = '';
    
    // Check if exists
    const existing = cart.find(item => item.id === id);
    if(existing) {
        if(existing.qty < maxQty) {
            existing.qty++;
            renderCart();
        } else {
            Swal.fire({ toast:true, position:'top-end', icon:'warning', title:'Stock limit reached', showConfirmButton:false, timer:2000 });
        }
        return;
    }
    
    cart.push({ id, name, price, maxQty, qty: 1 });
    renderCart();
}

function updateQty(id, delta) {
    const itemIndex = cart.findIndex(item => item.id === id);
    if(itemIndex > -1) {
        const item = cart[itemIndex];
        const newQty = item.qty + delta;
        
        if(newQty > 0 && newQty <= item.maxQty) {
            item.qty = newQty;
        } else if (newQty <= 0) {
            cart.splice(itemIndex, 1);
        } else {
            Swal.fire({ toast:true, position:'top-end', icon:'warning', title:'Not enough stock', showConfirmButton:false, timer:2000 });
        }
        renderCart();
    }
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartContainer');
    const invoiceBody = document.getElementById('invoiceBody');
    const emptyMsg = document.getElementById('emptyCartMsg');
    
    container.innerHTML = '';
    invoiceBody.innerHTML = '';
    let total = 0;
    
    if(cart.length === 0) {
        emptyMsg.style.display = 'block';
        container.style.display = 'none';
        document.getElementById('cartSubtotal').innerText = '₹0.00';
        document.getElementById('cartTotal').innerText = '₹0.00';
        document.getElementById('invoiceTotal').innerText = '₹0.00';
        document.getElementById('btnCheckout').disabled = true;
        document.getElementById('btnPrint').disabled = true;
        return;
    }
    
    emptyMsg.style.display = 'none';
    container.style.display = 'block';
    document.getElementById('btnCheckout').disabled = false;
    document.getElementById('btnPrint').disabled = true; // wait for checkout
    
    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        
        // Render Cart item
        container.innerHTML += `
            <div class="cart-item">
                <div style="flex: 1;">
                    <h6 class="fw-bold mb-1">${item.name}</h6>
                    <span class="text-muted small">₹${parseFloat(item.price).toFixed(2)} unit</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)"><i data-lucide="minus" width="14"></i></button>
                        <span class="fw-semibold text-center" style="width: 25px;">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)"><i data-lucide="plus" width="14"></i></button>
                    </div>
                    <div class="fw-bold text-deep-blue text-end" style="width: 70px;">
                        ₹${subtotal.toFixed(2)}
                    </div>
                </div>
            </div>
        `;
        
        // Render Invoice row
        invoiceBody.innerHTML += `
            <tr>
                <td class="fw-semibold">${item.name}<br><small class="text-muted">₹${parseFloat(item.price).toFixed(2)}</small></td>
                <td class="text-center align-middle">${item.qty}</td>
                <td class="text-end fw-bold align-middle">₹${subtotal.toFixed(2)}</td>
            </tr>
        `;
    });
    
    lucide.createIcons();
    
    const totalStr = '₹' + total.toFixed(2);
    document.getElementById('cartSubtotal').innerText = totalStr;
    document.getElementById('cartTotal').innerText = totalStr;
    document.getElementById('invoiceTotal').innerText = totalStr;
}

// Checkout mapping for print
document.getElementById('customerSelect').addEventListener('change', function() {
    const text = this.options[this.selectedIndex].text;
    document.getElementById('printCustomerName').innerText = text;
});

window.addEventListener('beforeprint', function() {
    document.getElementById('printCustomerInfo').classList.remove('d-none');
});
window.addEventListener('afterprint', function() {
    document.getElementById('printCustomerInfo').classList.add('d-none');
});

function processCheckout() {
    const customer_id = document.getElementById('customerSelect').value;
    
    const formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('cart', JSON.stringify(cart));
    if(customer_id) formData.append('customer_id', customer_id);
    
    // Show loader on button
    const btn = document.getElementById('btnCheckout');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
    btn.disabled = true;
    
    fetch('api/billing_api.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = '<i data-lucide="check-circle" width="18" class="me-2"></i> Completed';
            lucide.createIcons();
            
            if(data.status === 'success') {
                Swal.fire({
                    title: 'Invoice Generated!',
                    text: data.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                document.getElementById('btnPrint').disabled = false;
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-circle" width="18" class="me-2"></i> Confirm & Generate';
                lucide.createIcons();
            }
        });
}

// Initial Call
renderCart();
</script>

<?php require_once 'includes/footer.php'; ?>
