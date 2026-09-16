<?php
// api/customer_crud.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        $name   = trim($_POST['name']          ?? '');
        $phone  = trim($_POST['phone']         ?? '');
        $email  = trim($_POST['email']         ?? '');
        $notes  = trim($_POST['history_notes'] ?? '');

        if ($name === '' || strlen($name) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Customer name is required (max 100 chars).']);
            exit;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO customers (name, phone, email, history_notes) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $phone, $email, $notes]);
        echo json_encode(['status' => 'success', 'message' => 'Customer added successfully!']);

    } elseif ($action === 'update') {
        $id    = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $name  = trim($_POST['name']          ?? '');
        $phone = trim($_POST['phone']         ?? '');
        $email = trim($_POST['email']         ?? '');
        $notes = trim($_POST['history_notes'] ?? '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        if ($name === '' || strlen($name) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Customer name is required (max 100 chars).']);
            exit;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        $stmt = $pdo->prepare(
            'UPDATE customers SET name=?, phone=?, email=?, history_notes=? WHERE id=?'
        );
        $stmt->execute([$name, $phone, $email, $notes, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully!']);

    } elseif ($action === 'fetch') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM customers WHERE id=?');
        $stmt->execute([$id]);
        $customer = $stmt->fetch();
        if ($customer) {
            echo json_encode(['status' => 'success', 'data' => $customer]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Customer not found.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log('[MediCore][customer_crud] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
}
