<?php
session_start();

/* ========================
   JSON ONLY – NO HTML EVER
======================== */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

/* ========================
   SESSION CHECK
======================== */
if (
    !isset($_SESSION['signup_email'], $_SESSION['userID']) ||
    empty($_SESSION['signup_email'])
) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['userID'];

/* ========================
   DATABASE CONNECTION
======================== */
$db = new mysqli('localhost', 'root', '', 'wallet_db');
if ($db->connect_error) {
    echo json_encode([
        'error' => 'Database connection failed',
        'details' => $db->connect_error
    ]);
    exit;
}

/* ========================
   REQUEST VALIDATION
======================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$action = $data['action'] ?? null;
if (!$action) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

/* ========================
   ACTION: HISTORY STATS
======================== */
if ($action === 'GetHistoryInfo') {

    $period = isset($data['period']) ? (int)$data['period'] : 30;

    $stmt = $db->prepare("CALL GetUserTransferStats(?, ?)");
    if (!$stmt) {
        echo json_encode(['error' => $db->error]);
        exit;
    }

    $stmt->bind_param('ii', $userId, $period);
    $stmt->execute();
    $stmt->bind_result($total_transfer, $total_received, $total_spent);
    $stmt->fetch();

    echo json_encode([
        'transaction_num' => (int)$total_transfer,
        'total_spent'     => (float)$total_spent,
        'total_received'  => (float)$total_received
    ]);

    $stmt->close();
    $db->next_result();
    exit;
}

/* ========================
   ACTION: LOAD USERS
======================== */
if ($action === 'loadUsers') {

    $stmt = $db->prepare("CALL GetInteractedUsers(?)");
    if (!$stmt) {
        echo json_encode(['error' => $db->error]);
        exit;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);

    $stmt->close();
    $db->next_result();
    exit;
}

/* ========================
   ACTION: USER ↔ USER TX
======================== */
if ($action === 'user-to-user_Info') {

    if (!isset($data['ID'])) {
        echo json_encode(['error' => 'Missing user ID']);
        exit;
    }

    $otherId = (int)$data['ID'];

    $stmt = $db->prepare("CALL GetTransactionsBetweenUsers(?, ?)");
    if (!$stmt) {
        echo json_encode(['error' => $db->error]);
        exit;
    }

    $stmt->bind_param('ii', $userId, $otherId);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);

    $stmt->close();
    $db->next_result();
    exit;
}

/* ========================
   UNKNOWN ACTION
======================== */
echo json_encode(['error' => 'Unknown action']);
exit;
