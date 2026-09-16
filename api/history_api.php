<?php
// api/history_api.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'order_details') {
    $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid invoice ID.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('
            SELECT bi.quantity, bi.subtotal, m.name AS medicine_name, m.price
            FROM billing_items bi
            LEFT JOIN medicines m ON bi.medicine_id = m.id
            WHERE bi.billing_id = ?
        ');
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orderStmt = $pdo->prepare('
            SELECT b.id, b.total_amount, b.invoice_date, c.name AS customer_name
            FROM billing b
            LEFT JOIN customers c ON b.customer_id = c.id
            WHERE b.id = ?
        ');
        $orderStmt->execute([$id]);
        $summary = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$summary) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            exit;
        }

        echo json_encode([
            'status'  => 'success',
            'summary' => $summary,
            'items'   => $items,
        ]);
    } catch (PDOException $e) {
        error_log('[MediCore][history_api] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
