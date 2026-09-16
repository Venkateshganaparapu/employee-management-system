<?php
// api/export_excel.php
// BUG FIX: Corrected column names to match actual database schema.
// Added session authentication.

require_once '../includes/env.php';
require_once '../includes/error_handler.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_auth();

if (ob_get_length()) ob_end_clean();

$filename = 'pharmacy_full_data_' . date('Ymd') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Header">
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#0F172A" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="DateStyle">
   <NumberFormat ss:Format="yyyy-mm-dd"/>
  </Style>
  <Style ss:ID="CurrencyStyle">
   <NumberFormat ss:Format="Standard"/>
  </Style>
 </Styles>

<?php
function renderWorksheet(PDO $pdo, string $name, string $query, array $headers): void {
    echo ' <Worksheet ss:Name="' . htmlspecialchars($name) . '">' . "\n";
    echo '  <Table>' . "\n";

    // Header Row
    echo '   <Row ss:StyleID="Header">' . "\n";
    foreach ($headers as $h) {
        echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>' . "\n";
    }
    echo '   </Row>' . "\n";

    // Data Rows
    try {
        $stmt = $pdo->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '   <Row>' . "\n";
            foreach ($row as $key => $val) {
                $type  = 'String';
                $style = '';
                $val   = $val ?? '';

                if (is_numeric($val) && $val !== '') {
                    $type = 'Number';
                    if (
                        stripos($key, 'price') !== false ||
                        stripos($key, 'total') !== false ||
                        stripos($key, 'amount') !== false ||
                        stripos($key, 'subtotal') !== false
                    ) {
                        $style = ' ss:StyleID="CurrencyStyle"';
                    }
                } elseif (
                    $val !== '' &&
                    (stripos($key, 'date') !== false || stripos($key, 'expiry') !== false) &&
                    strtotime((string)$val) !== false
                ) {
                    $type  = 'DateTime';
                    $style = ' ss:StyleID="DateStyle"';
                    $val   = date('Y-m-d\TH:i:s.000', strtotime((string)$val));
                }

                $cleanVal = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                echo '    <Cell' . $style . '><Data ss:Type="' . $type . '">' . $cleanVal . '</Data></Cell>' . "\n";
            }
            echo '   </Row>' . "\n";
        }
    } catch (PDOException $e) {
        error_log('[MediCore][export_excel] Worksheet "' . $name . '": ' . $e->getMessage());
    }

    echo '  </Table>' . "\n";
    echo '  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' . "\n";
    echo '   <FreezePanes/>' . "\n";
    echo '   <FrozenNoSplit/>' . "\n";
    echo '   <SplitHorizontal>1</SplitHorizontal>' . "\n";
    echo '   <TopRowBottomPane>1</TopRowBottomPane>' . "\n";
    echo '   <ActivePane>2</ActivePane>' . "\n";
    echo '  </WorksheetOptions>' . "\n";
    echo ' </Worksheet>' . "\n";
}

// ── 1. Sales Sheet (matches actual schema) ────────────────────────────────────
$qSales = "
    SELECT b.id AS Invoice_ID,
           DATE(b.invoice_date) AS Date,
           COALESCE(c.name, 'Walk-in Customer') AS Customer_Name,
           COALESCE(m.name, 'Unknown') AS Medicine_Name,
           bi.quantity AS Quantity_Sold,
           m.price AS Price_Per_Unit,
           bi.subtotal AS Subtotal,
           b.total_amount AS Invoice_Total
    FROM billing b
    LEFT JOIN customers c ON b.customer_id = c.id
    LEFT JOIN billing_items bi ON b.id = bi.billing_id
    LEFT JOIN medicines m ON bi.medicine_id = m.id
    ORDER BY b.id DESC
";
renderWorksheet($pdo, 'Sales', $qSales, [
    'Invoice_ID', 'Date', 'Customer_Name', 'Medicine_Name',
    'Quantity_Sold', 'Price_Per_Unit', 'Subtotal', 'Invoice_Total',
]);

// ── 2. Medicines Sheet ────────────────────────────────────────────────────────
$qMeds = "
    SELECT m.id AS Medicine_ID, m.name AS Name, m.category AS Category,
           m.price AS Price, m.quantity AS Stock,
           COALESCE(s.name, 'N/A') AS Supplier, m.expiry_date AS Expiry_Date
    FROM medicines m
    LEFT JOIN suppliers s ON m.supplier_id = s.id
    ORDER BY m.id ASC
";
renderWorksheet($pdo, 'Medicines', $qMeds, [
    'Medicine_ID', 'Name', 'Category', 'Price', 'Stock', 'Supplier', 'Expiry_Date',
]);

// ── 3. Customers Sheet (matches actual schema) ────────────────────────────────
$qCust = "
    SELECT id AS Customer_ID, name AS Name, phone AS Phone,
           email AS Email, history_notes AS History_Notes
    FROM customers ORDER BY id ASC
";
renderWorksheet($pdo, 'Customers', $qCust, [
    'Customer_ID', 'Name', 'Phone', 'Email', 'History_Notes',
]);

// ── 4. Suppliers Sheet ────────────────────────────────────────────────────────
$qSup = "
    SELECT id AS Supplier_ID, name AS Name, contact_person AS Contact_Person,
           phone AS Phone, email AS Email, status AS Status
    FROM suppliers ORDER BY id ASC
";
renderWorksheet($pdo, 'Suppliers', $qSup, [
    'Supplier_ID', 'Name', 'Contact_Person', 'Phone', 'Email', 'Status',
]);

echo '</Workbook>' . "\n";
exit;
