<?php
$hosts = ['127.0.0.1', 'localhost', '::1'];
$user = 'hg6ddb59_larav25';
$password = 'p33t70(G!S';
$db = 'hg6ddb59_larav25';

foreach ($hosts as $host) {
    echo "Trying $host...\n";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
        echo "CONNECTED to $host\n";

        $sql = "SELECT ft.id FROM financial_transactions ft 
                JOIN orders o ON ft.order_id = o.id 
                WHERE ft.type = 'revenue' 
                AND o.payment_status NOT IN ('paid', 'approved')";

        $stmt = $pdo->query($sql);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($ids) > 0) {
            echo "Found " . count($ids) . " entries. Deleting...\n";
            $pdo->exec("DELETE FROM financial_transactions WHERE id IN (" . implode(',', $ids) . ")");
            echo "DONE.\n";
        } else {
            echo "Nothing to delete.\n";
        }
        break;
    } catch (Exception $e) {
        echo "Failed $host: " . $e->getMessage() . "\n";
    }
}
