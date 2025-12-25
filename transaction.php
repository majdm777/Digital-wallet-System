<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: index.php'); // Redirect if not logged in
    exit;
}

$userId = $_SESSION['userID'];
$db = new mysqli('localhost', 'root', '', 'wallet_db');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}


$stmt = $db->prepare("
SELECT
    t.transfer_id,
    t.amount,
    t.Operation,
    t.created_at,

    t.sender_id,
    us.FirstName AS sender_name,
    us.Email AS sender_email,

    t.receiver_id,
    ur.FirstName AS receiver_name,
    ur.Email AS receiver_email,

    w.balance AS user_balance,


    CASE
        WHEN t.sender_id = ? THEN 'send'
        WHEN t.receiver_id = ? THEN 'received'
    END AS type

FROM transfers t, users us, users ur, wallets w
WHERE
    (t.sender_id = ? OR t.receiver_id = ?)
    AND us.user_id = t.sender_id
    AND ur.user_id = t.receiver_id
    AND w.User_id = ?
    
ORDER BY t.created_at ;



");

$stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate running (remaining) balance per transaction
$runningBalance = 0;
foreach ($transactions as $index => $tran) {
    switch ($tran['Operation']) {
        case 'Deposit':
            $runningBalance += $tran['amount'];
            break;
        case 'Cash-Out':
            $runningBalance -= $tran['amount'];
            break;
        case 'Cash-Send':
            $runningBalance += ($tran['sender_id'] == $userId) ? -$tran['amount'] : $tran['amount'];
            break;
    }
    $transactions[$index]['remaining_balance'] = $runningBalance;
}

// Reverse array so newest transactions appear on top
// $transactions = array_reverse($transactions);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Transactions.CSS">
    <title>Transactions</title>
</head>
<body>
<div class="Main">
    <div class="nav-bar">
        <div class="Return-button"><a href="Main.html" style="text-decoration: none; color: inherit;">Return</a></div>
        <div ><?php echo htmlspecialchars("UserId:".$userId); ?></div>
    </div>
    <hr>
    <div class="transactions-box">
        <?php if (empty($transactions)): ?>
            <p class="emptymsge">No Transaction Yet</p>
        <?php else: ?>
            <?php foreach ($transactions as $tran): ?>
                <div class="Transaction-box">
                    <!-- Transaction header -->
                    <div class="transaction-In">
                        <div class="From-To Transaction-In-All">
                            <div class="From-tran"><?php echo htmlspecialchars($tran["sender_name"]."-".$tran['sender_id']); ?></div>
                            <div class="tran-icon"><img src="IMAGES/transaction.png" width="30" height="30"></div>
                            <div class="To-tran"><?php echo htmlspecialchars($tran["receiver_name"]."-".$tran['receiver_id']); ?></div>
                        </div>
                        <div class="Type Transaction-In-All"><?php echo htmlspecialchars($tran['Operation']); ?></div>
                        <div class="Amount Transaction-In-All"><?php echo number_format($tran['amount'], 2); ?>$</div>
                        <div class="Tran_code Transaction-In-All">-<?php echo htmlspecialchars($tran['transfer_id']); ?>-</div>
                    </div>

                    <!-- Transaction details (expandable) -->
                    <div class="Transaction-Out">
                        <!-- Balance info -->
                        <div class="Tran-Balance Transaction-Out-All">

                            <div class="balance-info-row">Spent: <span class="B1"><?php echo number_format($tran['amount'], 2); ?>$</span></div>
                            <hr style="width:50%;">
                            <div class="balance-info-row">
                                Remaining Balance:
                                <span class="B1"><?php echo number_format($tran['remaining_balance'], 2); ?>$</span>
                            </div>                            
                        </div>

                        <!-- Users info -->
                        <div class="Users Transaction-Out-All">
                            <div class="User-box-info">
                                <span class="User-Id"><?php echo htmlspecialchars($tran["sender_name"]."-".$tran['sender_id']); ?></span>
                                <span class="User-email"><?php echo htmlspecialchars($tran['sender_email']); ?></span>
                            </div>
                            <div class="Tran-type"><?php echo htmlspecialchars($tran['type']); ?></div>
                            <div class="User-box-info">
                                <span class="User-Id"><?php echo htmlspecialchars($tran["receiver_name"]."-".$tran['receiver_id']); ?></span>
                                <span class="User-email"><?php echo htmlspecialchars($tran['receiver_email']); ?></span>
                            </div>
                        </div>

                        <!-- Transaction metadata and remove button -->
                        <div class="Tran-Info Transaction-Out-All">
                            <div class="infos">Date: <?php echo htmlspecialchars($tran['created_at']); ?></div>
                            <div class="infos">Type: <?php echo htmlspecialchars($tran['Operation']); ?></div>
                            <!-- <div class="infos">Code: <?php echo htmlspecialchars($tran['transaction_code']); ?></div> -->
                            <div class="infos">-<?php echo htmlspecialchars($tran['transfer_id']); ?>-</div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- JS for expand/collapse -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const transactions = document.querySelectorAll(".Transaction-box");
    transactions.forEach(tran => {
        tran.addEventListener("click", () => {
            tran.classList.toggle("active");
        });
    });
});
</script>
</body>
</html>
