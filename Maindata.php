<?php
session_start();

if (!isset($_SESSION["signup_email"]) || empty($_SESSION['signup_email'])) {
            $_SESSION['error'] = "an error happened while signing you in, please login again";
            header("Location: index.php");
            exit();
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

        $query = "SELECT SUM(amount) FROM transfers WHERE sender_id=?";
        $query1 = "SELECT SUM(amount) FROM transfers WHERE receiver_id=?";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }

        $stmt->bind_param('i', $userId);

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->store_result();
        $stmt->bind_result($spen);
        $stmt->fetch();
        $spend = $spen;
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
            "received" => $received,

        ]);
        $stmt->free_result();

        exit;
    }


    if ($action === "CheckBalance") {
        $balance = (float) $data['money'];
        $query = "SELECT balance FROM wallets WHERE User_id=? AND balance >= ?";

        $stat = $db->prepare($query);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('id', $userId, $balance);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $result = $stat->get_result();
        header('Content-Type: application/json');
        if ($result->num_rows == 1) {

            echo json_encode([
                "approved" => true,
            ]);

        } else {
            echo json_encode([
                "approved" => false,
            ]);
        }
        exit;

    }


    if ($action === "receiverExistence") {
        header('Content-Type: application/json');
        $receiverid = $data['receiver'];
        trim($receiverid);
        $receiverFname=substr($receiverid,0, strpos($receiverid, '-') );
        $receiverid = substr($receiverid, strpos($receiverid, '-') + 1);

        $query = "SELECT * FROM users WHERE user_id=? AND FirstName=?";
        if ($receiverid == $userId) {
            echo json_encode([
                "exist" => 0,

            ]);
            exit;
        }
        $stat = $db->prepare($query);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('is', $receiverid,$receiverFname);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $result = $stat->get_result();

        if ($result->num_rows == 1) {

            echo json_encode([
                "exist" => 1,
            ]);
        } else {
            echo json_encode([
                "exist" => -1,
            ]);
        }
        exit;

    }

    if ($action === "transferMoney") {//should be reviewed
        $receiverid = $data['receiver_id'];
        trim($receiverid);
        $receiverid = substr($receiverid, strpos($receiverid, '-') + 1);
 
        $Type = $data['type'];
        $amount = (float)$data['amount'];
        $transfer_code =(int)$data['transaction_code'];

        $query = "CALL money_transfer(?,?,?,?,?,@flag)";//red flag
        $stat = $db->prepare($query);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('iiids', $transfer_code, $userId, $receiverid, $amount, $Type);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $stat->close();
        $result = $db->query("SELECT @flag as status");
        $row = $result->fetch_assoc();

        if ($row['status']) {
            echo json_encode([
                "comment" => "transfer succeeded"
            ]);
        } else {
            echo json_encode([
                "comment" => "transfer failed",
            ]);
        }

        exit;

    }
    if($action === 'CashOut'){
        $amount=(float)$data['amount'];

        $query="CALL CashOut(?,?,@flag)";
        $stat=$db->prepare($query);
        $stat->bind_param('id',$userId,$amount);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }  
        $stat->close();
        $result=$db->query('SELECT @flag AS status');
        $row=$result->fetch_assoc();
         if ($row['status']) {
            echo json_encode([
                "comment" => "withdraw request has been sent successfully"
            ]);
        } else {
            echo json_encode([
                "comment" => "withdraw request failed",
            ]);
        } 
        exit;       

    }

    if($action === 'CheckUserRequest'){
        

        $query="SELECT * FROM withdrawals WHERE User_id=? AND status='pending'";
        $stat=$db->prepare($query);
        $stat->bind_param('i',$userId);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }  
        $result = $stat->get_result();
        
        
         if ($result->num_rows == 0) {
            echo json_encode([
                "approve" => true
            ]);
        } else {
            echo json_encode([
                "approve" => false
            ]);
        }        
        exit;
    }

    if($action ==="operations"){
        $query="CALL GetUserTransactions(?)";
        $stmt=$db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($transactions);



    }


    if($action === "GetWithdrawalInfo"){
        $query="SELECT withdrawal_id,amount,created_at FROM withdrawals WHERE User_id=? AND status='pending'";
        $stmt=$db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($transactions);

    }


    if($action === "removeRequest"){
        $query="CALL RemoveRequest(?,@flag)";
        $stat=$db->prepare($query);
        $stat->bind_param('i',$userId);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }  
        $stat->close();
        $result=$db->query('SELECT @flag AS status');
        $row=$result->fetch_assoc();
         if ($row['status']) {
            echo json_encode([
                "comment" => "withdraw request has been removed"
            ]);
        } else {
            echo json_encode([
                "comment" => "failed to remove the request",
            ]);
        } 
        exit;       

    }

    if($action === "logout"){

        $_SESSION = [];
        session_destroy();

        echo json_encode(["logout" => true]);
        exit;
    }

}


$db->close();
?>