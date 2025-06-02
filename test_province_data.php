<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'dict';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking data for May 2025:\n\n";
    
    // Check tech_support_requests in May 2025
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tech_support_requests WHERE date_requested >= '2025-05-01' AND date_requested < '2025-06-01'");
    $result = $stmt->fetch();
    echo "Tech support requests in May 2025: " . $result['total'] . "\n";
    
    // Check tech_support_requests with client data
    $stmt = $pdo->query("
        SELECT tsr.id, tsr.support_type, tsr.date_requested, c.province_id, p.province_name
        FROM tech_support_requests tsr 
        LEFT JOIN clients c ON tsr.client_id = c.id 
        LEFT JOIN provinces p ON c.province_id = p.id
        WHERE tsr.date_requested >= '2025-05-01' AND tsr.date_requested < '2025-06-01'
        ORDER BY tsr.date_requested
    ");
    $requests = $stmt->fetchAll();
    
    echo "\nTech support requests with province data:\n";
    foreach($requests as $req) {
        echo "ID: {$req['id']}, Type: {$req['support_type']}, Date: {$req['date_requested']}, Province: " . ($req['province_name'] ?? 'NULL') . "\n";
    }
    
    // Check provinces
    $stmt = $pdo->query("SELECT id, province_name FROM provinces ORDER BY province_name");
    $provinces = $stmt->fetchAll();
    echo "\nAvailable provinces:\n";
    foreach($provinces as $province) {
        echo "ID: {$province['id']}, Name: {$province['province_name']}\n";
    }
    
    // Check clients and their provinces
    $stmt = $pdo->query("SELECT c.id, c.client_name, c.province_id, p.province_name FROM clients c LEFT JOIN provinces p ON c.province_id = p.id");
    $clients = $stmt->fetchAll();
    echo "\nClients and their provinces:\n";
    foreach($clients as $client) {
        echo "Client ID: {$client['id']}, Name: {$client['client_name']}, Province: " . ($client['province_name'] ?? 'NULL') . "\n";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
