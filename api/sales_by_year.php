<?php
// api/sales_by_year.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();
header('Content-Type: application/json');

$year = filter_var($_GET['year'] ?? date('Y'), FILTER_VALIDATE_INT);
if (!$year || $year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

try {
    $stmt = $pdo->prepare('
        SELECT MONTH(invoice_date) AS month,
               IFNULL(SUM(total_amount), 0) AS total
        FROM billing
        WHERE YEAR(invoice_date) = ?
        GROUP BY MONTH(invoice_date)
        ORDER BY month ASC
    ');
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_fill(0, 12, 0.0);
    foreach ($rows as $row) {
        $data[(int)$row['month'] - 1] = (float)$row['total'];
    }

    echo json_encode(array_values($data));
} catch (PDOException $e) {
    error_log('[MediCore][sales_by_year] ' . $e->getMessage());
    echo json_encode(array_fill(0, 12, 0.0));
}
