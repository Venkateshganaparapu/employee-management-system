<?php
// action_discount.php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_discount'])) {
    $discount = floatval($_POST['discount']);
    if ($medicine && $discount > 0 && $discount <= 100) {
        try {
            $newPrice = $medicine['price'] - ($medicine['price'] * ($discount / 100));
            $update = $pdo->prepare("UPDATE medicines SET price = ? WHERE id = ?");
            $update->execute([$newPrice, $id]);
            $success = "Successfully applied a " . $discount . "% discount. The new price is $" . number_format($newPrice, 2);
            $medicine['price'] = $newPrice; // Update UI data
        } catch (PDOException $e) {
            $error = "Error applying discount: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a valid discount percentage (1-100).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Discount - MediCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h3 class="text-primary fw-bold mb-4"><i data-lucide="tag" class="me-2"></i> Apply Discount</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger bg-opacity-10 text-danger border border-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success bg-opacity-10 text-success border border-success fw-semibold"><i data-lucide="check-circle" class="me-2"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($medicine): ?>
                <div class="bg-white border rounded-3 p-3 mb-4">
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($medicine['name']) ?></h5>
                    <p class="text-muted mb-2">Batch #<?= $medicine['id'] ?> &bull; Current Stock: <?= $medicine['quantity'] ?></p>
                    <div class="alert bg-primary bg-opacity-10 text-primary border-0 mb-0 d-inline-block px-3 py-2 fw-semibold">
                        Current Price: $<?= number_format($medicine['price'], 2) ?>
                    </div>
                </div>
                
                <form method="POST">
                    <label class="form-label text-muted small fw-semibold">Discount Percentage (%)</label>
                    <div class="input-group mb-4">
                        <span class="input-group-text bg-light text-muted"><i data-lucide="percent" width="18"></i></span>
                        <input type="number" name="discount" class="form-control px-3 border-start-0" placeholder="e.g., 10" min="1" max="100" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" name="apply_discount" class="btn btn-primary px-4 shadow-sm">Apply Discount</button>
                        <button type="button" class="btn btn-light border px-4" onclick="window.close();">Cancel</button>
                    </div>
                </form>
            <?php elseif ($success): ?>
                <div class="mt-4">
                    <button class="btn btn-primary px-4" onclick="window.close();">Close Window</button>
                </div>
            <?php else: ?>
                <a href="alerts.php" class="btn btn-light border px-4">Back to Alerts</a>
            <?php endif; ?>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
