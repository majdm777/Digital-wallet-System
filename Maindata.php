<?php
session_start();

if (!isset($_SESSION["signup_email"]) || empty($_SESSION['signup_email'])) {
    //handle erro
    //r;
}
$userEmail = $_SESSION["signup_email"];
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
        if (!empty($userEmail)) {
            $query_spent = "SELECT SUM(amount) FROM transfers 
                WHERE type = ? 
                    AND sender_id = (SELECT user_id FROM users WHERE Email = ?)";

            $query_gained = "SELECT SUM(amount) FROM transfers 
            WHERE type = ? 
            AND sender_id = (SELECT user_id FROM users WHERE Email = ?)";

            $stmt1 = $db->prepare($query_spent);
            $stmt2 = $db->prepare($query_gained);

            if (!$stmt1 || !$stmt2) {
                die("Prepare failed: " . $db->error);
            }

            $send = "sned";
            $received = "received";

            $stmt1->bind_param('ss', $send, $userEmail);
            $stmt2->bind_param('ss', $received, $userEmail);

            if (!$stmt1->execute() || !$stmt2->execute()) {
                die("Execute failed: " . $db->error);
            }

            $stmt1->bind_result($total_spent);
            $stmt2->bind_result($total_received);


            $stmt1->fetch();
            $stmt2->fetch();

            header('Content-Type: application/json');
            echo json_encode([
                'total_spent' => $total_spent ?: 0,
                'total_received' => $total_received ?: 0
            ]);
            exit;
        }
        echo json_encode([
            'total_spent' =>  0,
            'total_received' =>  0
        ]);
        exit;

    }

}


$db->close();
?>