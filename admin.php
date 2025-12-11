<?php


$host = 'localhost';
$dbname = 'digital_wallet';
$username = 'root';
$password = '';

$conn = null;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Connection failed - will use mock data
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    // Mock data for testing
    $mockUsers = [
        'USER001' => [
            'id' => 'USER001',
            'name' => 'John Doe',
            'balance' => '2500.00',
            'transactions' => [
                ['id' => 'TXN001', 'date' => '2024-01-15 14:30:00', 'description' => 'Deposit', 'amount' => '500.00', 'type' => 'credit'],
                ['id' => 'TXN002', 'date' => '2024-01-14 10:15:00', 'description' => 'Purchase at Store', 'amount' => '-150.50', 'type' => 'debit'],
                ['id' => 'TXN003', 'date' => '2024-01-13 16:45:00', 'description' => 'Refund', 'amount' => '75.25', 'type' => 'credit'],
            ]
        ],
        'USER002' => [
            'id' => 'USER002',
            'name' => 'Jane Smith',
            'balance' => '5750.75',
            'transactions' => [
                ['id' => 'TXN004', 'date' => '2024-01-16 09:20:00', 'description' => 'Salary Deposit', 'amount' => '3000.00', 'type' => 'credit'],
                ['id' => 'TXN005', 'date' => '2024-01-15 18:30:00', 'description' => 'Online Payment', 'amount' => '-200.00', 'type' => 'debit'],
            ]
        ],
        'USER003' => [
            'id' => 'USER003',
            'name' => 'Bob Johnson',
            'balance' => '1250.50',
            'transactions' => [
                ['id' => 'TXN006', 'date' => '2024-01-17 12:00:00', 'description' => 'Transfer In', 'amount' => '500.00', 'type' => 'credit'],
                ['id' => 'TXN007', 'date' => '2024-01-16 15:30:00', 'description' => 'ATM Withdrawal', 'amount' => '-100.00', 'type' => 'debit'],
                ['id' => 'TXN008', 'date' => '2024-01-15 11:20:00', 'description' => 'Bill Payment', 'amount' => '-250.00', 'type' => 'debit'],
            ]
        ]
    ];
    
    if ($_GET['action'] === 'search' && isset($_GET['userId'])) {
        $userId = $_GET['userId'];
        $found = false;
        
        if ($conn) {
            try {
                // Get user info
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Get transactions
                    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
                    $stmt->execute([$userId]);
                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'user' => [
                            'id' => $user['user_id'],
                            'name' => $user['name'],
                            'balance' => $user['balance']
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
                    $found = true;
                }
            } catch(PDOException $e) {
                // Database connection or query failed, will use mock data
            }
        }
        
        // Use mock data if database fails or user not found
        if (!$found) {
            if (isset($mockUsers[$userId])) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $mockUsers[$userId]['id'],
                        'name' => $mockUsers[$userId]['name'],
                        'balance' => $mockUsers[$userId]['balance']
                    ],
                    'transactions' => $mockUsers[$userId]['transactions']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
        }
        exit;
    }
    
    if ($_GET['action'] === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        exit;
    }
    
    if ($_GET['action'] === 'suspend') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => true, 'message' => 'User suspended successfully']);
        exit;
    }
    
    if ($_GET['action'] === 'addFunds') {
        $data = json_decode(file_get_contents('php://input'), true);
        $newBalance = 3000.00; // Mock new balance
        echo json_encode(['success' => true, 'newBalance' => number_format($newBalance, 2, '.', '')]);
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
