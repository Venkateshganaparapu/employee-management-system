<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Increase time and memory limits
set_time_limit(300);
ini_set('memory_limit', '512M');

try {
    // 1. Schema Modifications (Ensuring columns exist)
    $pdo->exec("ALTER TABLE billing ADD COLUMN IF NOT EXISTS payment_method VARCHAR(20) DEFAULT 'Cash'");
    $pdo->exec("ALTER TABLE customers ADD COLUMN IF NOT EXISTS age INT DEFAULT 30");
    $pdo->exec("ALTER TABLE customers ADD COLUMN IF NOT EXISTS gender VARCHAR(10) DEFAULT 'Other'");
    $pdo->exec("ALTER TABLE medicines ADD COLUMN IF NOT EXISTS reorder_level INT DEFAULT 20");

    // Clear old data
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE billing_items");
    $pdo->exec("TRUNCATE TABLE billing");
    $pdo->exec("TRUNCATE TABLE medicines");
    $pdo->exec("TRUNCATE TABLE customers");
    $pdo->exec("TRUNCATE TABLE suppliers");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $pdo->beginTransaction();

    // 2. Generate Suppliers (10-20)
    $supplierBaseNames = ['Sun Pharma', 'Cipla', 'Dr. Reddy\'s', 'Lupin', 'Aurobindo', 'Zydus', 'Torrent', 'Glenmark', 'Mankind', 'Biocon'];
    $numSuppliers = rand(15, 20);
    $supplierIds = [];
    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < $numSuppliers; $i++) {
        $name = $supplierBaseNames[$i % count($supplierBaseNames)] . " " . (rand(0,1) ? "Labs" : "Pharma");
        $stmt->execute([$name, "Manager " . ($i+1), "9" . rand(100000000, 999999999), strtolower(str_replace(' ', '', $name)) . "@supplier.com"]);
        $supplierIds[] = $pdo->lastInsertId();
    }

    // 3. Generate Medicines (120-180)
    $medBaseNames = ['Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Azithromycin', 'Metformin', 'Insulin', 'Cetirizine', 'Omeprazole', 'Amlodipine', 'Losartan'];
    $cats = ['Pain Relief', 'Antibiotics', 'Allergy', 'Vitamins', 'Diabetes'];
    $numMeds = rand(150, 180);
    $medicineIds = [];
    $medicinePrices = [];
    $medicineStocks = [];
    $medicineWeights = [];
    $stmt = $pdo->prepare("INSERT INTO medicines (name, category, quantity, price, supplier_id, expiry_date, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < $numMeds; $i++) {
        $name = $medBaseNames[$i % count($medBaseNames)] . " " . (rand(1, 10) * 50) . "mg";
        $cat = $cats[array_rand($cats)];
        $qty = rand(20, 300);
        $price = rand(10, 500) + (rand(0, 99)/100);
        $sup = $supplierIds[array_rand($supplierIds)];
        $exp = date('Y-m-d', strtotime('+' . rand(6, 36) . ' months'));
        $reorder = rand(10, 40);
        
        $stmt->execute([$name, $cat, $qty, $price, $sup, $exp, $reorder]);
        $id = $pdo->lastInsertId();
        $medicineIds[] = $id;
        $medicinePrices[$id] = $price;
        $medicineStocks[$id] = $qty;
        
        $weight = (rand(1, 10) > 7) ? 5 : 1;
        for($w=0; $w<$weight; $w++) $medicineWeights[] = $id;
    }

    // 4. Generate Customers (200-350)
    $firstNames = ['Aarav', 'Saanvi', 'Arjun', 'Aadhya', 'Vihaan', 'Ananya', 'Vivaan', 'Pari', 'Advik', 'Anushka'];
    $lastNames = ['Sharma', 'Verma', 'Gupta', 'Patel', 'Singh', 'Kumar', 'Rao', 'Desai'];
    $numCust = rand(250, 350);
    $customerIds = [];
    $customerWeights = [];
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, history_notes, age, gender) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < $numCust; $i++) {
        $name = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
        $phone = rand(7000000000, 9999999999);
        $email = strtolower(str_replace(' ', '.', $name)) . rand(10,99) . "@email.com";
        $age = rand(18, 75);
        $gender = (rand(0,1) ? "Male" : "Female");
        
        $stmt->execute([$name, $phone, $email, "Regular patient.", $age, $gender]);
        $id = $pdo->lastInsertId();
        $customerIds[] = $id;
        
        $weight = (rand(1, 10) > 7) ? 4 : 1;
        for($w=0; $w<$weight; $w++) $customerWeights[] = $id;
    }

    // 5. Generate Sales (1000-1500)
    $numSales = rand(1200, 1500);
    $paymentMethods = ['Cash', 'UPI', 'Card'];
    $currentBillingId = 1;
    $allBilling = [];
    $allBillingItems = [];
    
    for ($i = 0; $i < $numSales; $i++) {
        $cust = $customerWeights[array_rand($customerWeights)];
        $date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 365) . ' days -' . rand(0, 23) . ' hours'));
        $pay = $paymentMethods[array_rand($paymentMethods)];
        
        $itemsCount = rand(1, 3);
        $total = 0;
        $usedMeds = [];
        
        for ($j = 0; $j < $itemsCount; $j++) {
            $medId = $medicineWeights[array_rand($medicineWeights)];
            if (isset($usedMeds[$medId])) continue;
            $usedMeds[$medId] = true;
            
            $qty = rand(1, 5);
            $price = $medicinePrices[$medId];
            $sub = $qty * $price;
            $total += $sub;
            
            $allBillingItems[] = "($currentBillingId, $medId, $qty, $sub)";
            $medicineStocks[$medId] = max(0, $medicineStocks[$medId] - $qty);
        }
        
        if ($total > 0) {
            $allBilling[] = "($cust, $total, '$date', '$pay')";
            $currentBillingId++;
        }
    }

    // Batch Inserts for Sales
    if (!empty($allBilling)) {
        $chunks = array_chunk($allBilling, 500);
        foreach ($chunks as $chunk) {
            $pdo->exec("INSERT INTO billing (customer_id, total_amount, invoice_date, payment_method) VALUES " . implode(',', $chunk));
        }
    }
    if (!empty($allBillingItems)) {
        $chunks = array_chunk($allBillingItems, 500);
        foreach ($chunks as $chunk) {
            $pdo->exec("INSERT INTO billing_items (billing_id, medicine_id, quantity, subtotal) VALUES " . implode(',', $chunk));
        }
    }

    // Update Medicine Stocks
    foreach ($medicineStocks as $id => $qty) {
        $pdo->prepare("UPDATE medicines SET quantity = ? WHERE id = ?")->execute([$qty, $id]);
    }

    $pdo->commit();

    // Verification
    $finalSales = $pdo->query("SELECT COUNT(*) FROM billing")->fetchColumn();
    $finalMeds = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();

    echo json_encode([
        'success' => true, 
        'message' => "Successfully generated $finalMeds medicines and $finalSales sales transactions.",
        'details' => [
            'medicines' => $finalMeds,
            'customers' => count($customerIds),
            'sales' => $finalSales
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Critical Error: ' . $e->getMessage()]);
}
?>
