<?php
session_start();

if (!isset($_SESSION["signup_email"]) || empty($_SESSION['signup_email'])) {
    //handle erro
    //r;
}
$userEmail = $_SESSION["signup_email"];
$userId=$_SESSION['userID'];
try {
    $db = new mysqli('localhost', 'root', '', 'wallet_db');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;
    if ($action === 'getuserinfo') {
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
        // $stmt->free_result();


        header('Content-Type: application/json');
        echo json_encode([
            'id' => $ID,
            'fname' => $fname,
            'email' => $email,
            'balance' => $balance,
            'walletid' => $walletid,

        ]);
        exit;
    }

    if ($action === 'getBalance') {
        
        $query="SELECT SUM(amount) FROM transfers WHERE sender_id=?";
        $query1="SELECT SUM(amount) FROM transfers WHERE receiver_id=?";
        $stmt = $db->prepare($query);
        
        if (!$stmt ) {
            die("Prepare failed: " . $db->error);
        }

        $stmt->bind_param('i', $userId);
        
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->store_result();
        $stmt->bind_result($spen);
        $stmt->fetch();
        $spend=$spen;
        $stmt->free_result();
        
        $stmt1 = $db->prepare($query1);
        $stmt1->bind_param('i', $userId);
        $stmt1->execute();
        $stmt1->store_result();
        $stmt1->bind_result($received);
        $stmt1->fetch();


        





        header('Content-Type: application/json');
        echo json_encode([
            "spend" => $spend,
            "received" =>$received,

        ]);
        $stmt->free_result();

        exit;
    }


    if($action === "CheckBalance"){
        $balance = (float)$data['money'];
        $query="SELECT balance FROM wallets WHERE User_id=? AND balance >= ?";

        $stat = $db->prepare($query);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('id', $userId,$balance);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $result = $stat->get_result();
        header('Content-Type: application/json');
        if($result->num_rows == 1){
        
        echo json_encode([
            "approved" => true,            
        ]);            
        }else{
         echo json_encode([
            "approved" => false,]);            
        }

    }


    if($action === "receiverExistence"){
        $receiverid=$data['receiver'];
        $query="SELECT * FROM users WHERE user_id=?";

        $stat = $db->prepare($query);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('i', $receiverid);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $result = $stat->get_result();
        header('Content-Type: application/json');
        if($result->num_rows == 1){
        
            echo json_encode([
            "exist" => true,            
            ]);            
        }else{
            echo json_encode([
            "exist" => false,]);            
        }
                

    }

}


$db->close();
?>