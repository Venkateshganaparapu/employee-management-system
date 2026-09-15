<?php
try {
    require 'c:/xampp/htdocs/pharmacy_system/includes/db.php';
    $hash = password_hash('venkypassword', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE username = 'admin'");
    $stmt->execute(['hash' => $hash]);
    echo "Password updated successfully to fresh hash.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
