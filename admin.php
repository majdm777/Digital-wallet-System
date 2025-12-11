<?php


$host = 'localhost';
$dbname = 'mirna_db';
$username = 'root';
$password = 'NewPassword123!';

$conn = null;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If connection fails, stop and return a JSON error.
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'search' && isset($_GET['userId'])) {
        $userId = $_GET['userId'];
        
        try {
            // Get user info
            $stmt = $conn->prepare("SELECT user_id, name, balance, status FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Get transactions
                $stmt = $conn->prepare("SELECT transaction_id, transaction_date, description, amount, type FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
                $stmt->execute([$userId]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['user_id'],
                        'name' => $user['name'],
                        'balance' => $user['balance'],
                        'status' => $user['status']
                    ],
                    'transactions' => array_map(function($t) {
                        return [
                            'id' => $t['transaction_id'],
                            'date' => $t['transaction_date'],
                            'description' => $t['description'],
                            'amount' => $t['amount'],
                            'type' => $t['type']
                        ];
                    }, $transactions)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'A database error occurred while searching.']);
        }
        exit;
    }
    
    if ($_GET['action'] === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // It's good practice to delete related records first
            $stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Then delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);

            $conn->commit();

            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } catch (PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }
        exit;
    }
    
    if ($_GET['action'] === 'suspend') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        // We can also log the reason and duration if we had columns for them

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        try {
            // Assuming your 'users' table has a 'status' column (e.g., ENUM 'active', 'suspended', 'deleted')
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'User suspended successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found or already suspended.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to suspend user.']);
        }
        exit;
    }
    
    if ($_GET['action'] === 'addFunds') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $amount = $data['amount'] ?? 0;

        if (!$userId || !is_numeric($amount) || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Valid User ID and positive amount are required.']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // 1. Update user's balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
            $stmt->execute([$amount, $userId]);

            // 2. Get the new balance
            $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $newBalance = $stmt->fetchColumn();

            // 3. Create a new transaction record
            $transactionId = 'TXN' . strtoupper(uniqid());
            $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, user_id, type, amount, description) VALUES (?, ?, 'credit', ?, 'Admin Deposit')");
            $stmt->execute([$transactionId, $userId, $amount]);

            $conn->commit();

            echo json_encode(['success' => true, 'newBalance' => number_format($newBalance, 2, '.', '')]);
        } catch (PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add funds.']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Wallet Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>Digital Wallet Admin Panel</h1>
        </header>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Section - Search -->
            <div class="section search-section">
                <h2>Search User</h2>
                <div class="search-container">
                    <input type="text" id="userIdSearch" placeholder="Enter User ID..." class="search-input">
                    <button id="searchBtn" class="btn btn-primary">Search</button>
                </div>
                <div id="userInfo" class="user-info hidden">
                    <h3>User Details</h3>
                    <p><strong>User ID:</strong> <span id="displayUserId"></span></p>
                    <p><strong>Name:</strong> <span id="displayUserName"></span></p>
                    <p><strong>Balance:</strong> $<span id="displayUserBalance"></span></p>
                </div>
            </div>

            <!-- Middle Section - Transactions -->
            <div class="section transactions-section">
                <h2>Transaction History</h2>
                <div class="search-container" style="margin-bottom: 15px;">
                    <input type="text" id="transactionSearch" placeholder="Search transactions..." class="search-input">
                </div>
                <div id="transactionsList" class="transactions-list">
                    <p class="placeholder-text">Search for a user to view transactions</p>
                </div>
            </div>

            <!-- Right Section - Actions -->
            <div class="section actions-section">
                <h2>User Actions</h2>
                <div id="actionsContainer" class="actions-container hidden">
                    <button id="deleteBtn" class="btn btn-danger">Delete User</button>
                    <button id="suspendBtn" class="btn btn-warning">Suspend User</button>
                    <button id="addBtn" class="btn btn-success">Add Funds</button>
                </div>
                <p id="actionsPlaceholder" class="placeholder-text">Click on user details to see actions</p>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">

        <form action="admin.php" method="post" class="modal-content">
            <h2>Delete User</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="deleteUserId" class="form-input" readonly name="deleteUserId">
            </div>
            <div class="form-group">
                <label>Reason for Deletion:</label>
                <input type="text" name="delete-description" id="deleteReason" class="form-textarea" placeholder="Enter reason for deleting this user..." rows="4">
            </div>
            <div class="modal-actions">
                <button id="deleteCancelBtn" class="btn btn-secondary">Cancel</button>
                <input type="submit" name="submit1" id="deleteConfirmBtn" class="btn btn-danger" value="Confirm Delete">
            </div>
        </form>

    </div>

    <!-- Suspend Modal -->
    <div id="suspendModal" class="modal">
        <form method="post" action="admin.php" class="modal-content">
            <h2>Suspend User</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="suspendUserId" class="form-input" name="suspendUserId" readonly>
            </div>
            <div class="form-group">
                <label>Reason for Suspension:</label>
                <input type="text" id="suspendReason" class="form-textarea" placeholder="Enter reason for suspending this user..." rows="4">
            </div>
            <div class="form-group">
                <label>Suspension Duration:</label>
                <input type="text" id="suspendDuration" class="form-input" placeholder="e.g., 30 days, 6 months, permanent">
            </div>
            <div class="modal-actions">
                <button id="suspendCancelBtn" class="btn btn-secondary">Cancel</button>
                <input type="submit" name="submit2" id="suspendConfirmBtn" class="btn btn-warning" value="Confirm Suspend">
            </div>
        </form>
    </div>

    <!-- Add Funds Modal -->
    <div id="addModal" class="modal">
        <form method="post" action="admin.php" class="modal-content">
            <h2>Add Funds</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="addUserId" class="form-input" readonly name="fundsUserId">
            </div>
            <div class="form-group">
                <label>Amount to Add:</label>
                <input type="number" id="addAmount" class="form-input" placeholder="Enter amount..." step="0.01" min="0">
            </div>
            <div class="modal-actions">
                <button id="addCancelBtn" class="btn btn-secondary">Cancel</button>
                <input type="submit" name="submit3" id="addConfirmBtn" class="btn btn-success" value="Confirm Add">
            </div>
        </form>
    </div>

    <script src="admin.js"></script>
</body>
</html>
