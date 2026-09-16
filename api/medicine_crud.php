<?php
// api/medicine_crud.php
require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();                        // Must be logged in
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── Input sanitisation helpers ────────────────────────────────────────────────
function sanitise_medicine(array $post): array|false {
    $name        = trim($post['name']        ?? '');
    $category    = trim($post['category']    ?? '');
    $quantity    = $post['quantity']          ?? '';
    $price       = $post['price']             ?? '';
    $supplier_id = $post['supplier_id']       ?? '';
    $expiry_date = trim($post['expiry_date']  ?? '');

    if ($name === '' || strlen($name) > 100)          return false;
    if ($category === '' || strlen($category) > 50)   return false;
    if (!ctype_digit((string)$quantity) || (int)$quantity < 0) return false;
    if (!is_numeric($price) || (float)$price < 0)     return false;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)) return false;

    return [
        'name'        => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'category'    => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
        'quantity'    => (int)$quantity,
        'price'       => round((float)$price, 2),
        'supplier_id' => ($supplier_id !== '' && ctype_digit((string)$supplier_id))
                            ? (int)$supplier_id : null,
        'expiry_date' => $expiry_date,
    ];
}

try {
    if ($action === 'create') {
        $data = sanitise_medicine($_POST);
        if ($data === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO medicines (name, category, quantity, price, supplier_id, expiry_date)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'], $data['category'], $data['quantity'],
            $data['price'], $data['supplier_id'], $data['expiry_date'],
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Medicine added successfully.']);

    } elseif ($action === 'update') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $data = sanitise_medicine($_POST);
        if ($data === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'UPDATE medicines SET name=?, category=?, quantity=?, price=?, supplier_id=?, expiry_date=?
             WHERE id=?'
        );
        $stmt->execute([
            $data['name'], $data['category'], $data['quantity'],
            $data['price'], $data['supplier_id'], $data['expiry_date'], $id,
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Medicine updated successfully.']);

    } elseif ($action === 'apply_discount') {
        $id       = filter_var($_POST['id']       ?? '', FILTER_VALIDATE_INT);
        $discount = filter_var($_POST['discount']  ?? '', FILTER_VALIDATE_FLOAT);

        if (!$id || $discount === false || $discount <= 0 || $discount > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID or discount value.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT price FROM medicines WHERE id=?');
        $stmt->execute([$id]);
        $med = $stmt->fetch();
        if ($med) {
            $newPrice = round($med['price'] - ($med['price'] * ($discount / 100)), 2);
            $pdo->prepare('UPDATE medicines SET price=? WHERE id=?')->execute([$newPrice, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Discount applied.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Medicine not found.']);
        }

    } elseif ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $pdo->prepare('DELETE FROM medicines WHERE id=?')->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Medicine deleted successfully.']);

    } elseif ($action === 'fetch') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM medicines WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Medicine not found.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log('[MediCore][medicine_crud] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
}
