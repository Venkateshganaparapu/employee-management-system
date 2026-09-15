<?php
// action_dispose.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
$error = '';
$success = '';
$medicine = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$id]);
    $medicine = $stmt->fetch();
}

if (!$medicine) {
    $error = "Medicine not found or invalid ID.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_dispose'])) {
    if ($medicine) {
        try {
            $delete = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
            $delete->execute([$id]);
            $success = "Medicine successfully disposed and removed from inventory.";
            $medicine = null; // hide details after delete
        } catch (PDOException $e) {
            $error = "Error disposing medicine: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispose Medicine - MediCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h3 class="text-danger fw-bold mb-4"><i data-lucide="trash-2" class="me-2"></i> Dispose Medicine</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success fw-semibold"><i data-lucide="check-circle" class="me-2"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($medicine): ?>
                <div class="bg-white border rounded-3 p-3 mb-4">
                    <h5 class="fw-bold"><?= htmlspecialchars($medicine['name']) ?></h5>
                    <p class="text-muted mb-1">Batch #<?= $medicine['id'] ?> &bull; Stock: <?= $medicine['quantity'] ?></p>
                    <p class="text-danger fw-semibold mb-0">Expired on: <?= date('M d, Y', strtotime($medicine['expiry_date'])) ?></p>
                </div>
                
                <form method="POST">
                    <p class="fw-semibold">Are you absolutely sure you want to completely remove this medicine from the inventory?</p>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" name="confirm_dispose" class="btn btn-danger px-4">Confirm Dispose</button>
                        <button type="button" class="btn btn-light border px-4" onclick="window.close();">Cancel</button>
                    </div>
                </form>
            <?php elseif ($success): ?>
                <div class="mt-4">
                    <button class="btn btn-primary" onclick="window.close();">Close Window</button>
                    <!-- Fallback if window.close doesn't work -->
                    <a href="alerts.php" class="btn btn-link">Back to Alerts</a>
                </div>
            <?php else: ?>
                <a href="alerts.php" class="btn btn-light border">Back to Alerts</a>
            <?php endif; ?>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
