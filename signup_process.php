<?php
session_start();

$db = new mysqli('localhost','root','','wallet_db');
if ($db->connect_error) die("DB error");

/* ===== SECURITY CHECK ===== */
if (empty($_SESSION['signup_verified']) || $_SESSION['signup_verified'] !== true) {
    header("Location: SIGNUP.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit3'])) {

    $email = $_SESSION['signup_email'] ?? '';
    $first = trim($_POST['First-Name'] ?? '');
    $last  = trim($_POST['Last-Name'] ?? '');
    $dob   = trim($_POST['Date-Of-Birth'] ?? '');
    $nat   = trim($_POST['Nationality'] ?? '');
    $pass  = trim($_POST['Password'] ?? ''); // FIXED
    $addr  = trim($_POST['Address'] ?? '');
    $inc   = trim($_POST['Income-Source'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $type  = trim($_POST['Type-Of-Account'] ?? '');

    if (!$email || !$first || !$last || !$pass) {
        die("Missing required fields");
    }

    /* ===== TRANSACTION ===== */
    $db->begin_transaction();

    try {

        // ✅ AUTO_INCREMENT is better — but keeping your ID logic minimal
        do {
            $ID = random_int(10000, 99999);
            $check = $db->prepare("SELECT 1 FROM users WHERE user_id=?");
            $check->bind_param('i', $ID);
            $check->execute();
            $check->store_result();
        } while ($check->num_rows > 0);

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            "INSERT INTO users
            (user_id, FirstName, LastName, Email, Birthday, Nationality, Address, Phone, Type_Of_Account, Income_source, Password)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            'issssssssss',
            $ID, $first, $last, $email, $dob, $nat, $addr, $phone, $type, $inc, $hash
        );
        $stmt->execute();

        $stmt2 = $db->prepare("INSERT INTO wallets (User_id) VALUES (?)");
        $stmt2->bind_param('i', $ID);
        $stmt2->execute();

        $db->commit();

        unset($_SESSION['signup_code'], $_SESSION['signup_verified']);

        header("Location: signup_success.html");
        exit();

    } catch (Exception $e) {
        $db->rollback();
        die("Signup failed");
    }
}

$db->close();
