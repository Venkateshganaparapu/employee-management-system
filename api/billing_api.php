<?php
// api/billing_api.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    if ($action === 'search') {
        $query = trim($_POST['query'] ?? '');
        if (strlen($query) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Search query too long.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT id, name, price, quantity FROM medicines
             WHERE name LIKE ? AND quantity > 0 LIMIT 10'
        );
        $stmt->execute(["%$query%"]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'checkout') {
        $customer_id = !empty($_POST['customer_id'])
            ? filter_var($_POST['customer_id'], FILTER_VALIDATE_INT)
            : null;

        // customer_id must be a valid positive integer or null
        if ($customer_id !== null && ($customer_id === false || $customer_id <= 0)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid customer ID.']);
            exit;
        }

        $rawCart = $_POST['cart'] ?? '';
        $cart    = json_decode($rawCart, true);

        if (!is_array($cart) || empty($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
            exit;
        }

        // Validate each cart item
        foreach ($cart as $item) {
            if (
                !isset($item['id'], $item['qty'], $item['price']) ||
                !filter_var($item['id'],  FILTER_VALIDATE_INT) ||
                !filter_var($item['qty'], FILTER_VALIDATE_INT) ||
                !is_numeric($item['price']) ||
                (int)$item['qty']   <= 0 ||
                (float)$item['price'] < 0
            ) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid cart item data.']);
                exit;
            }
        }

        $pdo->beginTransaction();

        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += (float)$item['price'] * (int)$item['qty'];
        }

        // Verify stock is still available (prevent overselling)
        foreach ($cart as $item) {
            $stockStmt = $pdo->prepare('SELECT quantity FROM medicines WHERE id = ? FOR UPDATE');
            $stockStmt->execute([(int)$item['id']]);
            $stock = $stockStmt->fetchColumn();
            if ($stock === false || $stock < (int)$item['qty']) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Insufficient stock for one or more items.']);
                exit;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO billing (customer_id, total_amount) VALUES (?, ?)');
        $stmt->execute([$customer_id ?: null, round($total_amount, 2)]);
        $billing_id = $pdo->lastInsertId();

        $stmt_item  = $pdo->prepare(
            'INSERT INTO billing_items (billing_id, medicine_id, quantity, subtotal) VALUES (?, ?, ?, ?)'
        );
        $stmt_stock = $pdo->prepare(
            'UPDATE medicines SET quantity = quantity - ? WHERE id = ?'
        );

        foreach ($cart as $item) {
            $sub = round((float)$item['price'] * (int)$item['qty'], 2);
            $stmt_item->execute([$billing_id, (int)$item['id'], (int)$item['qty'], $sub]);
            $stmt_stock->execute([(int)$item['qty'], (int)$item['id']]);
        }

        $pdo->commit();

        echo json_encode([
            'status'     => 'success',
            'message'    => 'Invoice created successfully!',
            'invoice_id' => $billing_id,
        ]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[MediCore][billing_api] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
}
