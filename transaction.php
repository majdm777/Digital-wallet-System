<!--<!DOCTYPE html>
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
            <div class="Future-idea"></div>
            <div class="Delete-button" onclick="DeleteTransactions()">Delete All</div>
        </div>
        <hr>
        <div class="transactions-box">
        </div>
        
       




    </div>


 <script src="Transaction.js"></script> 
    
</body>
</html> -->


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

// Delete all transactions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $stmt = $db->prepare("DELETE FROM transfers WHERE sender_id = ? OR receiver_id = ?");
    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: transaction.php');
    exit;
}

// Delete single transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $stmt = $db->prepare("DELETE FROM transfers WHERE transfer_id = ?");
    $stmt->bind_param('i', $delId);
    $stmt->execute();
    $stmt->close();
    header('Location: transaction.php');
    exit;
}

// Fetch transactions
$stmt = $db->prepare("
    SELECT transfer_id, sender_id, receiver_id, amount, Operation, created_at 
    FROM transfers 
    WHERE sender_id = ? OR receiver_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param('ii', $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();
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
            <div class="Return-button">
                <a href="Main.html" style="text-decoration: none; color: inherit;">Return</a>
            </div>
            <div class="Future-idea"></div>
            <form method="POST" style="display: inline;">
                <button type="submit" name="delete_all" class="Delete-button">Delete All</button>
            </form>
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
                                <div class="From-tran"><?php echo htmlspecialchars($tran['sender_id']); ?></div>
                                <div class="tran-icon">
                                    <img src="IMAGES/transaction.png" width="30px" height="30px">
                                </div>
                                <div class="To-tran"><?php echo htmlspecialchars($tran['receiver_id']); ?></div>
                            </div>
                            <div class="Type Transaction-In-All"><?php echo htmlspecialchars($tran['Operation']); ?></div>
                            <div class="Amount Transaction-In-All"><?php echo number_format($tran['amount'], 2); ?>$</div>
                            <div class="Tran_code Transaction-In-All">-<?php echo htmlspecialchars($tran['transfer_id']); ?>-</div>
                        </div>

                        <!-- Transaction details (expandable) -->
                        <div class="Transaction-Out">
                            <!-- Balance info -->
                            <div class="Tran-Balance Transaction-Out-All">
                                <div class="balance-info-row">Wallet: <span class="B1"><?php echo number_format($tran['wallet_balance'], 2); ?>$</span></div>
                                <div class="balance-info-row">Spent: <span class="B1"><?php echo number_format($tran['amount'], 2); ?>$</span></div>
                                <hr style="width:50%;">
                                <div class="balance-info-row">Remain: <span class="B1"><?php echo number_format($tran['remaining_balance'], 2); ?>$</span></div>
                            </div>

                            <!-- Users info -->
                            <div class="Users Transaction-Out-All">
                                <div class="User-box-info">
                                    <span class="User-Id"><?php echo htmlspecialchars($tran['sender_id']); ?></span>
                                    <span class="User-email"><?php /* add sender email if available */ ?></span>
                                </div>
                                <div class="Tran-type"><?php echo htmlspecialchars($tran['Operation']); ?></div>
                                <div class="User-box-info">
                                    <span class="User-Id"><?php echo htmlspecialchars($tran['receiver_id']); ?></span>
                                    <span class="User-email"><?php /* add receiver email if available */ ?></span>
                                </div>
                            </div>

                            <!-- Transaction metadata and remove button -->
                            <div class="Tran-Info Transaction-Out-All">
                                <div class="infos">Date: <?php echo htmlspecialchars($tran['created_at']); ?></div>
                                <div class="infos">Type: <?php echo htmlspecialchars($tran['Operation']); ?></div>
                                <div class="infos">Code: <?php echo htmlspecialchars($tran['transaction_code']); ?></div>
                                <div class="infos">-<?php echo htmlspecialchars($tran['transfer_id']); ?>-</div>
                                <form method="POST">
                                    <input type="hidden" name="delete_id" value="<?php echo $tran['transfer_id']; ?>">
                                    <button type="submit" class="remove_transaction">Remove Transaction</button>
                                </form>
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
