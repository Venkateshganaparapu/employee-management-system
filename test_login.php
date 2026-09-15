<?php
try {
    require 'c:/xampp/htdocs/pharmacy_system/includes/db.php';
    $stmt = $pdo->query("SELECT * FROM users WHERE username='admin'");
    $user = $stmt->fetch();
    echo "HASH DB: " . $user['password_hash'] . "\n";
    echo "VERIFY venkypassword: " . (password_verify('venkypassword', $user['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
