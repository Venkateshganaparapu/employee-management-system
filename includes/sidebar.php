<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="bg-gradient-custom text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
            <i data-lucide="crosshair" width="18"></i>
        </div>
        <span class="text-glow">MediCore</span>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item-c">
            <a href="dashboard.php" class="nav-link-c <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
        </li>
        <li class="nav-item-c">
            <a href="medicines.php" class="nav-link-c <?= ($current_page == 'medicines.php') ? 'active' : '' ?>">
                <i data-lucide="pill"></i> Medicines
            </a>
        </li>
        <li class="nav-item-c">
            <a href="billing.php" class="nav-link-c <?= ($current_page == 'billing.php') ? 'active' : '' ?>">
                <i data-lucide="credit-card"></i> Billing / POS
            </a>
        </li>
        <li class="nav-item-c">
            <a href="customers.php" class="nav-link-c <?= ($current_page == 'customers.php') ? 'active' : '' ?>">
                <i data-lucide="users"></i> Customers
            </a>
        </li>
        <li class="nav-item-c">
            <a href="suppliers.php" class="nav-link-c <?= ($current_page == 'suppliers.php') ? 'active' : '' ?>">
                <i data-lucide="truck"></i> Suppliers
            </a>
        </li>
        <li class="nav-item-c">
            <a href="alerts.php" class="nav-link-c <?= ($current_page == 'alerts.php') ? 'active' : '' ?>">
                <i data-lucide="bell-ring"></i> Expiry Alerts
                <!-- Notification pill (static visual only for layout) -->
                <span class="badge bg-danger rounded-pill ms-auto">3</span>
            </a>
        </li>
        <li class="nav-item-c">
            <a href="history.php" class="nav-link-c <?= ($current_page == 'history.php') ? 'active' : '' ?>">
                <i data-lucide="history"></i> Sales History
            </a>
        </li>
        
        <hr class="mx-3 text-muted">
        
        <li class="nav-item-c">
            <a href="#" class="nav-link-c" id="theme-toggle">
                <i data-lucide="moon"></i> Dark Mode
            </a>
        </li>
        <li class="nav-item-c">
            <a href="login.php" class="nav-link-c text-danger">
                <i data-lucide="log-out"></i> Logout
            </a>
        </li>
    </ul>
</div>

<!-- Main Content Wrapper -->
<div class="main-content w-100 bg-body">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="toggle-btn" id="sidebar-toggle">
                <i data-lucide="menu"></i>
            </button>
            
            <div class="input-group d-none d-md-flex" style="width: 300px;">
                <span class="input-group-text bg-transparent border-end-0 border">
                    <i data-lucide="search" width="18" height="18" class="text-muted"></i>
                </span>
                <input type="text" id="globalSearch" class="form-control form-control-modern border-start-0 ps-0" placeholder="Global search..." style="padding: 8px 16px;">
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <a href="alerts.php" class="position-relative text-muted" style="cursor: pointer; text-decoration: none;">
                <i data-lucide="bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </a>
            
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar shadow-sm">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="d-none d-lg-block">
                        <span class="d-block fw-semibold text-dark mb-0" style="line-height:1"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                        <small class="text-muted" style="line-height:1"><?= htmlspecialchars($_SESSION['role'] ?? 'Admin') ?></small>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 15px;">
                    <li><a class="dropdown-item" href="dashboard.php"><i data-lucide="user" width="16" class="me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="medicines.php"><i data-lucide="settings" width="16" class="me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i data-lucide="log-out" width="16" class="me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Page Content Wrapper -->
    <div class="page-content-wrapper pb-5">
