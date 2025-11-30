<?php

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
        <div class="modal-content">
            <h2>Delete User</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="deleteUserId" class="form-input" readonly>
            </div>
            <div class="form-group">
                <label>Reason for Deletion:</label>
                <textarea id="deleteReason" class="form-textarea" placeholder="Enter reason for deleting this user..." rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button id="deleteCancelBtn" class="btn btn-secondary">Cancel</button>
                <button id="deleteConfirmBtn" class="btn btn-danger">Confirm Delete</button>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div id="suspendModal" class="modal">
        <div class="modal-content">
            <h2>Suspend User</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="suspendUserId" class="form-input" readonly>
            </div>
            <div class="form-group">
                <label>Reason for Suspension:</label>
                <textarea id="suspendReason" class="form-textarea" placeholder="Enter reason for suspending this user..." rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Suspension Duration:</label>
                <input type="text" id="suspendDuration" class="form-input" placeholder="e.g., 30 days, 6 months, permanent">
            </div>
            <div class="modal-actions">
                <button id="suspendCancelBtn" class="btn btn-secondary">Cancel</button>
                <button id="suspendConfirmBtn" class="btn btn-warning">Confirm Suspend</button>
            </div>
        </div>
    </div>

    <!-- Add Funds Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2>Add Funds</h2>
            <div class="form-group">
                <label>User ID:</label>
                <input type="text" id="addUserId" class="form-input" readonly>
            </div>
            <div class="form-group">
                <label>Amount to Add:</label>
                <input type="number" id="addAmount" class="form-input" placeholder="Enter amount..." step="0.01" min="0">
            </div>
            <div class="modal-actions">
                <button id="addCancelBtn" class="btn btn-secondary">Cancel</button>
                <button id="addConfirmBtn" class="btn btn-success">Confirm Add</button>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>
