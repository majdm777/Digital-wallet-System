<?php
session_start();

// Simple processing for the final signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit3'])) {
    // get email from hidden field (set earlier) or from session
    $email = trim($_POST['Email-Hidden'] ?? ($_SESSION['signup_email'] ?? ''));
    $first = trim($_POST['First-Name'] ?? '');
    $last = trim($_POST['Last-Name'] ?? '');
    $dob = trim($_POST['Date-Of-Birth'] ?? '');
    $nationality = trim($_POST['Nationality'] ?? '');
    $password = trim($_POST['Passwaord'] ?? '');
    $address = trim($_POST['Address'] ?? '');
    $income = trim($_POST['Income-Source'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $type = trim($_POST['Type-Of-Account'] ?? '');

    // Persist user to a simple CSV file (users.csv) — replace with DB in production
    // $row = [
    //     $email,
    //     $first,
    //     $last,
    //     $dob,
    //     $nationality,
    //     $phone,
    //     $type,
    //     $income,
    //     $address,
    //     date('Y-m-d H:i:s')
    // ];

    // $fp = fopen('users.csv', 'a');
    // if ($fp) {
    //     fputcsv($fp, $row);
    //     fclose($fp);
    // }

    // cleanup verification session
    if (isset($_SESSION)) {
        unset($_SESSION['signup_code'], $_SESSION['signup_verified']);
    }

    // redirect to a success page or display message
    if (!headers_sent()) {
        header('Location: signup_success.html');
        exit;
    } else {
        // Fallback redirect when headers already sent
        echo '<script>window.location="signup_success.html";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=signup_success.html"></noscript>';
        exit;
    }
}

// If reached without POST, show a small message
// (no output before header() calls above)
// echo 'No submission detected.';

?>