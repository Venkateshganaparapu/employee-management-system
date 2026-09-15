<?php
require_once 'includes/db.php';

try {
    $pdo->beginTransaction();

    // 1. Insert 5 more Suppliers
    $suppliers = [
        ['Global Meds India', 'Swati Chaturvedi', '555-0101', 'sarah@globalmeds.com'],
        ['HealthFirst Distributors', 'Brijesh Wadhwa', '555-0102', 'bruce@healthfirst.com'],
        ['Apex Pharma Pvt Ltd', 'Chirag Kulkarni', '555-0103', 'clark@apexpharma.com'],
        ['Arogya Supply Co.', 'Divya Pandey', '555-0104', 'diana@wellness.com'],
        ['BioLife Labs India', 'Balraj Ahuja', '555-0105', 'barry@biolife.com'],
    ];

    foreach ($suppliers as $s) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->execute($s);
    }
    
    // 2. Insert 10 more Customers
    $customers = [
        ['Esha Chauhan', '666-0201', 'esha@email.com', 'Requires liquid medications.'],
        ['Manish Reddy', '666-0202', 'manish@email.com', 'Regular refiller for BP medication.'],
        ['Sneha Joshi', '666-0203', 'sneha.j@email.com', 'Allergic to sulfa drugs.'],
        ['Deepak Kumar', '666-0204', 'deepak@email.com', 'No special notes.'],
        ['Priya Patel', '666-0205', 'priya@email.com', 'Prefers morning pickups.'],
        ['Jayesh Wagle', '666-0206', 'jayesh@email.com', 'Diabetic, buys insulin regularly.'],
        ['Mansi Gupta', '666-0207', 'mansi@email.com', 'Asthmatic, needs inhalers.'],
        ['Arhaan Ansari', '666-0208', 'arhaan@email.com', 'No special notes.'],
        ['Lata Bansal', '666-0209', 'lata@email.com', 'Takes supplements regularly.'],
        ['Tarun Haldar', '666-0210', 'tarun@email.com', 'Frequent purchaser of bandages.'],
    ];

    foreach ($customers as $c) {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, history_notes) VALUES (?, ?, ?, ?)");
        $stmt->execute($c);
    }
    
    $supplierIds = $pdo->query('SELECT id FROM suppliers')->fetchAll(PDO::FETCH_COLUMN);

    // 3. Insert 20 more Medicines
    $medicines = [
        ['Lisinopril 10mg', 'Cardio', 120, 15.50, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+6 months'))],
        ['Metformin 500mg', 'Diabetic', 300, 8.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+1 year'))],
        ['Amlodipine 5mg', 'Cardio', 150, 12.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+2 months'))],
        ['Metoprolol 50mg', 'Cardio', 90, 18.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+8 months'))],
        ['Albuterol Inhaler', 'Respiratory', 40, 45.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+1 year'))],
        ['Fluticasone Spray', 'Respiratory', 60, 22.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+14 days'))], // Expiring soon
        ['Sertraline 50mg', 'Antidepressant', 110, 16.50, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+2 years'))],
        ['Atorvastatin 20mg', 'Cholesterol', 200, 20.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+6 months'))],
        ['Pantoprazole 40mg', 'Antacid', 80, 12.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+4 days'))], // Expiring very soon
        ['Ciprofloxacin 500mg', 'Antibiotic', 130, 14.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+1 year'))],
        ['Azithromycin 250mg', 'Antibiotic', 100, 18.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('-5 days'))], // Expired!
        ['Prednisone 10mg', 'Steroid', 75, 10.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+10 months'))],
        ['Gabapentin 300mg', 'Neurology', 160, 24.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+3 years'))],
        ['Losartan 50mg', 'Cardio', 140, 19.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+5 months'))],
        ['Montelukast 10mg', 'Respiratory', 90, 28.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+1 year'))],
        ['Lexapro 10mg', 'Antidepressant', 85, 30.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+8 months'))],
        ['Meloxicam 15mg', 'NSAID', 220, 11.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+2 years'))],
        ['Tramadol 50mg', 'Analgesic', 65, 25.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+3 months'))],
        ['Clonazepam 1mg', 'Neurology', 50, 14.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+1 year'))],
        ['Zolpidem 10mg', 'Sleep Aid', 100, 21.00, $supplierIds[array_rand($supplierIds)], date('Y-m-d', strtotime('+9 months'))],
    ];

    foreach ($medicines as $m) {
        $stmt = $pdo->prepare("INSERT INTO medicines (name, category, quantity, price, supplier_id, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($m);
    }

    $customerIds = $pdo->query('SELECT id FROM customers')->fetchAll(PDO::FETCH_COLUMN);
    $medicineIds = $pdo->query('SELECT id FROM medicines')->fetchAll(PDO::FETCH_COLUMN);

    // 4. Insert 15 mock Billings / Invoices
    for ($i = 0; $i < 15; $i++) {
        $customerId = $customerIds[array_rand($customerIds)];
        $totalAmount = rand(15, 120) + (rand(0, 99) / 100);
        $daysAgo = rand(0, 30);
        $invDate = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
        
        $stmt = $pdo->prepare("INSERT INTO billing (customer_id, total_amount, invoice_date) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $totalAmount, $invDate]);
        $billingId = $pdo->lastInsertId();

        // 5. Insert billing items for this invoice
        $numItems = rand(1, 4);
        for ($j = 0; $j < $numItems; $j++) {
            $medId = $medicineIds[array_rand($medicineIds)];
            $qty = rand(1, 3);
            $subtotal = $qty * 10; // rough approx for dummy data
            $stmtItems = $pdo->prepare("INSERT INTO billing_items (billing_id, medicine_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
            $stmtItems->execute([$billingId, $medId, $qty, $subtotal]);
        }
    }

    $pdo->commit();
    echo "Dummy data seeded successfully!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Failed to seed data: " . $e->getMessage() . "\n";
}
?>
