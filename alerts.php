<?php
// alerts.php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Fetch medicines expiring within 30 days or already expired
$allAlerts = $pdo->query("
    SELECT * FROM medicines 
    WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
    ORDER BY expiry_date ASC
")->fetchAll();

$today = new DateTime('midnight');
?>

<style>
/* Alert Cards Specific Styles */
.alert-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition-normal);
    border: 1px solid rgba(239, 68, 68, 0.1); /* Red border base */
    position: relative;
    overflow: hidden;
}

body.dark-mode .alert-card {
    background: #1E293B;
    border-color: rgba(239, 68, 68, 0.2);
}

.alert-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
}

/* Severity Styling */
.severity-high { border-left: 5px solid #EF4444; } /* Expired or Today */
.severity-med { border-left: 5px solid #F97316; }  /* < 7 Days */
.severity-low { border-left: 5px solid #EAB308; }  /* < 30 Days */

.alert-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Pulsing animated bell for highest priority */
.pulse-bg {
    animation: pulseRed 2s infinite;
}

@keyframes pulseRed {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.countdown-box {
    background: var(--bg-body);
    border-radius: 12px;
    padding: 10px 15px;
    display: inline-block;
    font-family: monospace;
    font-size: 1.1rem;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2" data-aos="fade-down">
        <div>
            <h2 class="fw-bold text-deep-blue m-0 d-flex align-items-center gap-2">
                <i data-lucide="bell-ring" class="text-danger"></i> Expiry Alerts
            </h2>
            <p class="text-muted small mb-0">Critical monitoring for medicines nearing expiration.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="alerts.php?filter=all" class="text-decoration-none">
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 d-flex align-items-center gap-1 hover-effect">
                    <i data-lucide="alert-triangle" width="14"></i> <?= count($allAlerts) ?> Attention Required
                </span>
            </a>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="row g-3 mb-4" data-aos="fade-up">
        <?php 
            $count7 = 0; $count30 = 0; $countExpired = 0;
            $filteredAlerts = [];
            $filter = $_GET['filter'] ?? 'all';
            
            foreach($allAlerts as $m) {
                $exp = new DateTime($m['expiry_date']);
                $diff = $today->diff($exp)->invert ? -$today->diff($exp)->days : $today->diff($exp)->days;
                
                if($diff < 0) $countExpired++;
                elseif($diff <= 7) $count7++;
                else $count30++;

                // Filter logic
                $include = true;
                if ($filter === 'expired' && $diff >= 0) $include = false;
                if ($filter === '7days' && ($diff < 0 || $diff > 7)) $include = false;
                if ($filter === '30days' && $diff <= 7) $include = false;
                
                if ($include) {
                    $filteredAlerts[] = $m;
                }
            }
            $alerts = $filteredAlerts;
        ?>
        <div class="col-md-4">
            <a href="alerts.php?filter=expired" class="text-decoration-none d-block">
                <div class="glass-card p-3 border-start border-4 border-danger hover-effect">
                    <h5 class="fw-bold text-danger mb-1"><?= $countExpired ?></h5>
                    <p class="text-muted small mb-0 fw-semibold">Already Expired</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="alerts.php?filter=7days" class="text-decoration-none d-block">
                <div class="glass-card p-3 border-start border-4 border-warning hover-effect" style="border-color: #F97316 !important;">
                    <h5 class="fw-bold mb-1" style="color: #F97316"><?= $count7 ?></h5>
                    <p class="text-muted small mb-0 fw-semibold">Expiring in 7 Days</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="alerts.php?filter=30days" class="text-decoration-none d-block">
                <div class="glass-card p-3 border-start border-4 border-warning hover-effect">
                    <h5 class="fw-bold text-warning mb-1"><?= $count30 ?></h5>
                    <p class="text-muted small mb-0 fw-semibold">Expiring in 30 Days</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
        <?php if(empty($alerts)): ?>
            <div class="col-12 py-5 text-center text-muted">
                <i data-lucide="shield-check" width="64" class="text-success mb-3 opacity-50"></i>
                <h4 class="fw-bold">All Good!</h4>
                <p>No medicines are expiring within the next 30 days.</p>
            </div>
        <?php endif; ?>

        <?php foreach($alerts as $alert): 
            $expDate = new DateTime($alert['expiry_date']);
            $interval = $today->diff($expDate);
            $days = $interval->invert ? -$interval->days : $interval->days;
            
            // Determine severity and labels
            if ($days < 0) {
                $severityClass = 'severity-high';
                $iconClass = 'bg-danger text-white pulse-bg';
                $icon = 'alert-octagon';
                $statusText = 'Expired ' . abs($days) . ' days ago';
                $timeClass = 'text-danger';
            } elseif ($days === 0) {
                $severityClass = 'severity-high';
                $iconClass = 'bg-danger text-white pulse-bg';
                $icon = 'alert-triangle';
                $statusText = 'Expires TODAY';
                $timeClass = 'text-danger';
            } elseif ($days <= 7) {
                $severityClass = 'severity-med';
                $iconClass = 'bg-opacity-10 text-danger bg-danger';
                $icon = 'clock';
                $statusText = "Expires in $days days";
                $timeClass = 'text-warning' . ' text-opacity-75'; // Simulate orange
            } else {
                $severityClass = 'severity-low';
                $iconClass = 'bg-opacity-10 text-warning bg-warning';
                $icon = 'calendar-clock';
                $statusText = "Expires in $days days";
                $timeClass = 'text-muted';
            }
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="alert-card <?= $severityClass ?> h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="alert-icon-wrapper <?= $iconClass ?>">
                        <i data-lucide="<?= $icon ?>" width="24"></i>
                    </div>
                    <?php if($days <= 0): ?>
                        <a href="action_dispose.php?id=<?= $alert['id'] ?>" target="_blank" class="text-decoration-none">
                            <span class="badge bg-danger rounded-pill px-2 py-1"><i data-lucide="trash-2" width="12" class="me-1"></i> Dispose</span>
                        </a>
                    <?php else: ?>
                        <a href="action_discount.php?id=<?= $alert['id'] ?>" target="_blank" class="text-decoration-none">
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 hover-effect"><i data-lucide="tag" width="12" class="me-1"></i> Apply Discount</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($alert['name']) ?></h5>
                <div class="mb-3 d-flex gap-2 text-muted small">
                    <span>Batch #<?= $alert['id'] ?></span> • 
                    <span>Stock: <?= $alert['quantity'] ?></span>
                </div>
                
                <div class="mt-auto bg-light rounded-3 p-3">
                    <p class="small text-muted fw-semibold mb-1">Status:</p>
                    <div class="countdown-box w-100 text-center <?= $days <= 0 ? 'border border-danger border-opacity-50 text-danger' : 'text-dark' ?>">
                        <?= $statusText ?>
                    </div>
                    <div class="text-center mt-2 small text-muted">
                        Expires: <?= date('M d, Y', strtotime($alert['expiry_date'])) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.hover-effect {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-effect:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

<?php require_once 'includes/footer.php'; ?>
