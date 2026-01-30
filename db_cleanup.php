<?php
$dsn = 'mysql:host=localhost;dbname=hg6ddb59_larav25';
$user = 'hg6ddb59_larav25';
$password = 'p33t70(G!S';

try {
    $pdo = new PDO($dsn, $user, $password);
    echo "CONNECTED\n";

    // Find revenue transactions for non-paid orders
    $sql = "SELECT ft.id, ft.order_id, ft.amount, o.payment_status 
            FROM financial_transactions ft 
            JOIN orders o ON ft.order_id = o.id 
            WHERE ft.type = 'revenue' 
            AND o.payment_status NOT IN ('paid', 'approved')";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($rows) . " incorrect revenue transactions.\n";
    foreach ($rows as $row) {
        echo "Deleting FT ID: {$row['id']} (Order: {$row['order_id']}, Status: {$row['payment_status']})\n";
    }

    if (count($rows) > 0) {
        $ids = array_column($rows, 'id');
        $deleteSql = "DELETE FROM financial_transactions WHERE id IN (" . implode(',', $ids) . ")";
        $pdo->exec($deleteSql);
        echo "Deleted " . count($rows) . " transactions.\n";
    }

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage() . "\n";
}
