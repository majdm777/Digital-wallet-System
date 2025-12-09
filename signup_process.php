<?php
session_start();

try {
    $db = new mysqli('localhost','root','','wallet_db');
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
    $query="INSERT INTO users
            (user_id, FirstName, LastName, Email, Birthday, Nationality, Address, Phone, Type_Of_Account, Income_source,Password)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $query2="INSERT INTO wallets (User_id) VALUES (?)";
        $stmt = $db->prepare($query);
        $stmt1=$db->prepare($query2);

        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }

        do{
        $ID = random_int(10000, 99999);
        $query1="SELECT user_id FROM users WHERE user_id=?";
        $stat=$db->prepare($query1);
            if(!$stat) {
                die("Prepare failed: " . $db->error);
            }
        $stat->bind_param('i',$ID);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }        
        $result=$stat->get_result();
        }while($result->num_rows>0);


        $passHashed=password_hash($password,PASSWORD_DEFAULT);
        
        $stmt->bind_param('issssssssss', $ID,$first, $last , $email,$dob,$nationality,$address,$phone,$type,$income,$passHashed);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt1->bind_param('i',$ID);
        
        if (!$stmt1->execute()) {
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