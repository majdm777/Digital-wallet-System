<?php
session_start();
try {
    $db = new mysqli('localhost','root','','wallet_tester');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>"); 
}

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
    
    //adding user to the data base
    $query="INSERT INTO user 
            (ID, FirstName, LastName, Email, Date_of_birth, Nationality, Address, Phone, Type_of_Account, Income_Source,password)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $db->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }
        $ID = random_int(10000, 99999);

        
        $stmt->bind_param('issssssssss', $ID,$first, $last , $email,$dob,$nationality,$address,$phone,$type,$income,$password);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        


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
$db->close();
?>