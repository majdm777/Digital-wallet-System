<?php
session_start();

if(!isset($_SESSION["signup_email"]) || empty($_SESSION['signup_email'])){
    //handle error;
}
$userEmail=$_SESSION["signup_email"];
try {
    $db = new mysqli('localhost', 'root', '', 'wallet_db');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>");
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;
    if ($action === 'getuserinfo'){
        $query = "SELECT U.user_id, U.FirstName, U.Email, w.balance, w.wallet_id FROM users AS U JOIN wallets AS w ON U.user_id = w.User_id WHERE U.Email = ?";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }
        $stmt->bind_param('s', $userEmail);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->store_result();
        $stmt->bind_result($ID, $fname, $email, $balance, $walletid);
        $stmt->fetch();
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $ID,
            'fname' => $fname,
            'email' => $email,
            'balance' => $balance,
            'walletid' => $walletid
        ]);
        exit;    
    }




}

$db->close();
?>