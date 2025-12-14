<?php
session_start();

$message = "";
try {
    $db = new mysqli('localhost', 'root', '', 'wallet_db');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>");
}
$query1 = "SELECT  CheckUserExists(?)";
$query2 = "SELECT Password FROM users WHERE Email=?";
$query3 = "SELECT user_id FROM users WHERE Email=?";
if (isset($_POST['LOGIN'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];



    if (empty($email) || empty($password)) {
        $message = "Email and password are required";
    } else {

        $stat = $db->prepare($query1);
        if (!$stat) {
            die("Prepare failed: " . $db->error);
        }
        $stat->bind_param('s', $email);
        if (!$stat->execute()) {
            die("Execute failed: " . $stat->error);
        }
        $result = $stat->get_result();
        if($result->num_rows == 1){
            $stat1=$db->prepare($query2);
            if (!$stat1) {
                die("Prepare failed: " . $db->error);
            } 
            $stat1->bind_param('s',$email);
            if (!$stat1->execute()) {
                die("Execute failed: " . $stat1->error);
            }
            $stat1->store_result();
            
            $stat1->bind_result($passHashedDb);
            $stat1->fetch();
            $stat1->free_result();

            $stat2=$db->prepare($query3);
            $stat2->bind_param('s',$email);
            $stat2->execute();
            $stat2->bind_result($userId);
            $stat2->fetch();

            if(password_verify($password,$passHashedDb)){
                session_regenerate_id(true);
                $_SESSION["signup_email"]=$email;
                $_SESSION["userID"]=$userId ?? 0;
                header("Location: Main.html");
                exit();
            }else{
                $message = "Invalid email or password";
            }


            
        }else{
                $message = "Invalid email or password";
        }

    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="STYLESHEET.css">
    <title>LOG-IN page </title>
</head>

<body>

    <div class="wrapper">
        <div class="SIGN_IN_BOX">
            <form class="SIGN_IN_FORM" id="login-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <h3 class="LOGIN">LOG IN</h3>
                <p id="validation-message"><?php echo $message; ?></p>
                <input id="email" placeholder="Email" type="email" required="required" class="input-box" name="email"
                    oninput="resetMessage()">
                <input id="password" placeholder="Password" type="password" required="required" class="input-box"
                    oninput="show_icon(); resetMessage();" name="password">
                <span class="show-password" onclick="Show_password()"></span>
                <input type="submit" name="LOGIN" class="SIGN_IN_BUTTON" value="LOGIN">

            </form>
            <div class="OTHER_OPTIONS">
                <a class="mar1" href="ForgetPass.php">Forget Password?</a>
                <a class="mar1" href="SIGNUP.html">Don't Have An Account?</a>
            </div>
        </div>
    </div>

    <script src="LOGIN.js"></script>

</body>

</html>