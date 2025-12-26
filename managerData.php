<?php
session_start();


if(!isset($_SESSION["managerId"]) || empty($_SESSION['managerId'])){
            $_SESSION['error'] = "Manager session expired, please login again";
            header("Location: index.php");
            exit();
}
$managerId=$_SESSION["managerId"];
$managerEmail=$_SESSION['managerEmail'];
$managerusername=$_SESSION['managerUsername'];



function generateNumericTransactionId() {
    // Generates a random number between 10^9 and (10^10)-1
    return mt_rand(100000000, 999999999);
}


// 1. Turn off display_errors for production/API so they don't break JSON
ini_set('display_errors', 0); 
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli("localhost", "root", "", "wallet_db");
    if($_SERVER['REQUEST_METHOD']==='POST'){
            $data = json_decode(file_get_contents("php://input"), true);
            $action = $data['action'] ?? null;

    if ($action === "searchUser") {
        $userId = (int)$data['userId'];

        $stmt = $db->prepare("CALL getUserBasicInfo(?)");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result(); // Better way to fetch from procedures
        $user = $result->fetch_assoc();

        if ($user) {
            echo json_encode([
                'userId' => $user['user_id'], // Ensure these match your DB column names
                'userName' => $user['name'],
                'userEmail' => $user['Email'],
                'userBalance' => $user['balance'],
                'status' => $user['status']
            ]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }

        $stmt->close();
        // Clear remaining results from the stored procedure call
        while ($db->more_results()) {
            $db->next_result();
        }
    }
    if($action==="getWithdrawalsRequests"){
        $query= "CALL getPendingWithdrawals()";
        $stmt=$db->prepare($query);
        $stmt->execute();
        $result= $stmt->get_result();
        $requests= $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($requests);
        exit;
    }

    if($action==="handleRequest"){

        $withdrawalID= (int) $data['withdrawal_id'];
        $transactionId = generateNumericTransactionId();
        $query= "CALL handleRequests(?,?,?)";
        $stmt= $db->prepare($query);
        $stmt->bind_param('iii', $managerId, $withdrawalID, $transactionId);
        $stmt->execute();

        echo json_encode(["comment"=>"request handled."]);
        exit;
    }
    if($action==="getTransactions"){
        $userId= (int) $data['selectedUserId'];
        $query = "CALL GetUserTransactions(?)";
        $stmt= $db ->prepare($query);
        $stmt->bind_param('i',$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions= $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($transactions);
        $stmt->close();
        $db->next_result();
        exit;
    }

    if($action==="deleteUser"){
        $userId= (int) $data["userId"];
        
        $query = "CALL DeleteUserAccountSafely(?,?,@flag)";
        $stmt= $db->prepare($query);
        $stmt->bind_param('ii', $userId, $managerId);
        $stmt->execute();

        $result = $db->query("SELECT @flag AS status");
        $row = $result->fetch_assoc(); // associative array
        echo json_encode([
                "success" => (bool)($row['status'] ?? 0),
                "comment" => ($row['status'] ?? 0) ? "User deleted successfully" : "Deletion failed"]);

        exit;

    }

if ($action === "suspendUser") {

    $userId = (int) $data["userId"];

    $query = "CALL SuspendUserAccount(?, ?, @flag)";
    $stmt  = $db->prepare($query);
    $stmt->bind_param("ii", $userId, $managerId);
    $stmt->execute();
    $stmt->close();

    $result = $db->query("SELECT @flag AS status");
    $row    = $result->fetch_assoc();
    $flag   = (int) ($row["status"] ?? 0);

    // Handle all cases explicitly
    if ($flag === 2) {
        echo json_encode([
            "success" => true,
            "status"  => "suspended",
            "message" => "User suspended successfully"
        ]);

    } elseif ($flag === 1) {
        echo json_encode([
            "success" => true,
            "status"  => "active",
            "message" => "User unsuspended successfully"
        ]);

    } elseif ($flag === -1) {
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Action not allowed"
        ]);
    }

    exit;
}


if($action==="addFunds"){
    $transactionId = generateNumericTransactionId();
    $userId= (int) $data["userId"];
    $amount= (float) $data["amount"];
    

    $query = "CALL addFunds(?,?,?,@flag)";
    $stmt = $db-> prepare($query);
    $stmt->bind_param("iid", $transactionId,$userId, $amount);
    $stmt->execute();
    $stmt->close();

    $result = $db->query("SELECT @flag AS status");
    $row = $result->fetch_assoc();
    $flag = (int) ($row["status"] ?? 0);

    if ($flag === 1) {
        echo json_encode([
            "success" => true,
            "message" => "Funds added successfully"
        ]);
    
    }elseif ($flag === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Wallet not found"
        ]);
    }elseif ($flag === -1) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid amount"
        ]);
    }else
        echo json_encode([
            "success" => false,
            "message" => "Action not allowed"
        ]);
    exit;
}


    }
} catch (Exception $e) {
    // Catch any error and return it as clean JSON
    echo json_encode(['error' => $e->getMessage()]);
}exit;