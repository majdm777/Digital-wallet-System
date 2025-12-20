<?php 
session_start();

if (!isset($_SESSION["signup_email"]) || empty($_SESSION['signup_email'])) {
    //handle error

}
$userEmail = $_SESSION["signup_email"];
$userId = $_SESSION['userID'];
try {
    $db = new mysqli('localhost', 'root', '', 'wallet_db');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;


    if($action==="GetHistoryInfo"){
        $period=(Int)$data['period'] || 30;
        $query="Call GetUserTransferStats(?,?)";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }

        $stmt->bind_param('ii', $userId,$period);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->store_result();
        $stmt->bind_result($total_transfer, $total_received,$total_spent);
        $stmt->fetch();



        header('Content-Type: application/json');
        echo json_encode([
            'transaction_num' => $total_transfer,
            'total_spent' => $total_spent,
            'total_received' => $total_received,
            

        ]);
        $stmt->close();
        $db->next_result();
        exit;

    }

}

?>