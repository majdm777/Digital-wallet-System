<?php
$managerId=1;

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

    if($action==="suspendUser"){
        $userId= (int) $data["userId"];
        
        $query = "CALL SuspendUserAccount(?,?,@flag)";
        $stmt= $db->prepare($query);
        $stmt->bind_param('ii', $userId, $managerId);
        $stmt->execute();

        $result = $db->query("SELECT @flag AS status");
        $row = $result->fetch_assoc(); // associative array
        echo json_encode([
            "success" => (bool)($row['status'] ?? 0),
            "comment" => ($row['status'] ?? 0) ? "User suspended successfully" : "Suspention failed"]);

        exit;

    }

    }
} catch (Exception $e) {
    // Catch any error and return it as clean JSON
    echo json_encode(['error' => $e->getMessage()]);
}
exit;



























// header('Content-Type: application/json');

// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// try {
//     $db = new mysqli("localhost", "root", "NewPassword123!", "wallet_db");
// } catch (mysqli_sql_exception $e) {
//     echo json_encode(['error' => 'DB connection failed']);
//     exit;
// }
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $data = json_decode(file_get_contents("php://input"), true);
//     $action = $data['action'] ?? null;

//     if ($action === "searchUser") {

//         $userId = (int)$data['userId'];

//         $stmt = $db->prepare("CALL getUserBasicInfo(?)");
//         $stmt->bind_param("i", $userId);
//         $stmt->execute();
//         $stmt->bind_result($id, $name, $email, $balance);

//         if ($stmt->fetch()) {
//             echo json_encode([
//                 'userId' => $id,
//                 'userName' => $name,
//                 'userEmail' => $email,
//                 'userBalance' => $balance
//             ]);
//         } else {
//             echo json_encode(['error' => 'User not found']);
//         }

//         $stmt->close();
//         while ($db->more_results() && $db->next_result()) {}
//         exit;
//     }
// } 



// // Handle AJAX requests
// if (isset($_GET['action'])) {
//     header('Content-Type: application/json');
    
//     if ($_GET['action'] === 'search' && isset($_GET['userId'])) {
//         $userId = $_GET['userId'];
        
//         try {
//             // Get user info
//             $stmt = $conn->prepare("CALL GetUserBasicInfo(?)");
//             $stmt->execute([$userId]);
//             $user = $stmt->fetch(PDO::FETCH_ASSOC);
//             $stmt->closeCursor();
            
//             if ($user) {
//                 // Get transactions
//                 $stmt = $conn->prepare("CALL GetUserTransactions(?)");
//                 $stmt->execute([$userId]);
//                 $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
//                 $stmt->closeCursor();
                
//                 echo json_encode([
//                     'success' => true,
//                     'user' => [
//                         'id' => $user['user_id'],
//                         'name' => $user['name'],
//                         'balance' => $user['balance'],
                        
//                     ],
//                     'transactions' => array_map(function($t) {
//                         return [
//                             'id' => $t['transfer_id'],
//                             'date' => $t['created_at'],
//                             'description' => $t['Operation'],
//                             'amount' => $t['amount']
    
//                         ];
//                     }, $transactions)
//                 ]);
//             } else {
//                 echo json_encode(['success' => false, 'message' => 'User not found']);
//             }
//         } catch(PDOException $e) {
//             http_response_code(500);
//             echo json_encode(['success' => false, 'message' => 'A database error occurred while searching.']);
//         }
//         exit;
//     }
    
//     if ($_GET['action'] === 'delete') {
//         $data = json_decode(file_get_contents('php://input'), true);
//         $userId = $data['userId'] ?? null;

//         if (!$userId) {
//             http_response_code(400); // Bad Request
//             echo json_encode(['success' => false, 'message' => 'User ID is required.']);
//             exit;
//         }

//         try {
//             $conn->beginTransaction();

//             // It's good practice to delete related records first
//             $stmt = $conn->prepare("CALL DeleteUserAccount(?)");
//             $stmt->execute([$userId]);
//             $stmt->closeCursor();


//             // Then delete the user
//             $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
//             $stmt->execute([$userId]);

//             $conn->commit();

//             echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
//         } catch (PDOException $e) {
//             $conn->rollBack();
//             http_response_code(500);
//             echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
//         }
//         exit;
//     }
    
//     if ($_GET['action'] === 'suspend') {
//         $data = json_decode(file_get_contents('php://input'), true);
//         $userId = $data['userId'] ?? null;
//         // We can also log the reason and duration if we had columns for them

//         if (!$userId) {
//             http_response_code(400);
//             echo json_encode(['success' => false, 'message' => 'User ID is required.']);
//             exit;
//         }

//         try {
//             // Assuming your 'users' table has a 'status' column (e.g., ENUM 'active', 'suspended', 'deleted')
//             $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
//             $stmt->execute([$userId]);

//             if ($stmt->rowCount() > 0) {
//                 echo json_encode(['success' => true, 'message' => 'User suspended successfully']);
//             } else {
//                 echo json_encode(['success' => false, 'message' => 'User not found or already suspended.']);
//             }
//         } catch (PDOException $e) {
//             http_response_code(500);
//             echo json_encode(['success' => false, 'message' => 'Failed to suspend user.']);
//         }
//         exit;
//     }
    
//     if ($_GET['action'] === 'addFunds') {
//         $data = json_decode(file_get_contents('php://input'), true);
//         $userId = $data['userId'] ?? null;
//         $amount = $data['amount'] ?? 0;

//         if (!$userId || !is_numeric($amount) || $amount <= 0) {
//             http_response_code(400);
//             echo json_encode(['success' => false, 'message' => 'Valid User ID and positive amount are required.']);
//             exit;
//         }

//         try {
//             $conn->beginTransaction();

//             // 1. Update user's balance
//             $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
//             $stmt->execute([$amount, $userId]);

//             // 2. Get the new balance
//             $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
//             $stmt->execute([$userId]);
//             $newBalance = $stmt->fetchColumn();

//             // 3. Create a new transaction record
//             $transactionId = 'TXN' . strtoupper(uniqid());
//             $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, user_id, type, amount, description) VALUES (?, ?, 'credit', ?, 'Admin Deposit')");
//             $stmt->execute([$transactionId, $userId, $amount]);

//             $conn->commit();

//             echo json_encode(['success' => true, 'newBalance' => number_format($newBalance, 2, '.', '')]);
//         } catch (PDOException $e) {
//             $conn->rollBack();
//             http_response_code(500);
//             echo json_encode(['success' => false, 'message' => 'Failed to add funds.']);
//         }
//         exit;
//     }
//     if($_GET['action']==='fetchWithdrawals'){
//         try{
//             $stmt= $conn->prepare("CALL GetPendingWithdrawals()");
//             $stmt->execute();
//             $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
//             if($withdrawals){
//                 echo json_encode(['success' => true, 'withdrawals' => $withdrawals]);
//             }else{
//                 echo json_encode(['success' => false, 'message' => 'No pending withdrawals found.']);
//             }
//         } catch(PDOException $e){
//             http_response_code(500);
//             echo json_encode(['success' => false, 'message' => 'Failed to fetch withdrawals.']);
//         }
//         exit;
//     }
// }
// ?>
