<?php
session_start();

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    exit;
}

$userId = $_SESSION['userID'];

$db = new mysqli("localhost", "root", "", "wallet_db");
if ($db->connect_error) {
    die("Database connection failed");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null;

/*ACTION: GET USER INFO*/

if ($action === "getAccountInfo") {

    $query = "
        SELECT 
            user_id,
            FirstName,
            LastName,
            Email,
            Nationality,
            Birthday,
            Phone,
            Income_source,
            Type_Of_Account,
            Address
        FROM users
        WHERE user_id = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    header("Content-Type: application/json");
    echo json_encode($user);
    exit;
}

/*ACTION: UPDATE ACCOUNT FIELD*/

if ($action === "updateField") {

    $field = $data['field'] ?? "";
    $value = trim($data['value'] ?? "");

    $allowed = [
        "phone" => "Phone",
        "Address" => "Address",
        "Incomesrc" => "Income_source",
        "Type-Of-Acc" => "Type_Of_Account"
    ];

    if (!array_key_exists($field, $allowed)) {
        http_response_code(400);
        exit;
    }

    if ($field === "phone" && !preg_match('/^[0-9]{8,15}$/', $value)) {
        echo json_encode(["success" => false, "msg" => "Invalid phone"]);
        exit;
    }

    $column = $allowed[$field];
    $query = "UPDATE users SET $column = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $value, $userId);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

/*ACTION: DELETE ACCOUNT*/

if ($action === "deleteAccount") {

    $query = "CALL DeleteUserAccount(?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // IMPORTANT: clear remaining results
    while ($db->more_results() && $db->next_result()) {;}

    session_destroy();

    echo json_encode([
        "deleted" => true
    ]);
    exit;
}
