<?php
// api/supplier_crud.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        $name    = trim($_POST['name']           ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $phone   = trim($_POST['phone']          ?? '');
        $email   = trim($_POST['email']          ?? '');

        if ($name === '' || strlen($name) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Supplier name is required (max 100 chars).']);
            exit;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO suppliers (name, contact_person, phone, email, status) VALUES (?, ?, ?, ?, 'Active')"
        );
        $stmt->execute([$name, $contact, $phone, $email]);
        echo json_encode(['status' => 'success', 'message' => 'Supplier added successfully!']);

    } elseif ($action === 'update') {
        $id      = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $name    = trim($_POST['name']           ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $phone   = trim($_POST['phone']          ?? '');
        $email   = trim($_POST['email']          ?? '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        if ($name === '' || strlen($name) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Supplier name is required (max 100 chars).']);
            exit;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        $stmt = $pdo->prepare(
            'UPDATE suppliers SET name=?, contact_person=?, phone=?, email=? WHERE id=?'
        );
        $stmt->execute([$name, $contact, $phone, $email, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Supplier updated successfully!']);

    } elseif ($action === 'toggle_status') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT status FROM suppliers WHERE id=?');
        $stmt->execute([$id]);
        $current   = $stmt->fetchColumn();
        $newStatus = ($current === 'Active') ? 'Inactive' : 'Active';

        $pdo->prepare('UPDATE suppliers SET status=? WHERE id=?')->execute([$newStatus, $id]);
        echo json_encode(['status' => 'success', 'message' => "Supplier marked as $newStatus."]);

    } elseif ($action === 'fetch') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM suppliers WHERE id=?');
        $stmt->execute([$id]);
        $supplier = $stmt->fetch();
        if ($supplier) {
            echo json_encode(['status' => 'success', 'data' => $supplier]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Supplier not found.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log('[MediCore][supplier_crud] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
}
