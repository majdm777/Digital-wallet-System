<?php
session_start();

$message = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$db = new mysqli('localhost', 'root', '', 'wallet_db');
if ($db->connect_error) {
    die("Database connection failed");
}

if (isset($_POST['LOGIN'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: index.php");
        exit();
    }

    /* =======================
       MANAGER LOGIN
    ======================== */
    if (str_ends_with(strtolower($email), '@wallet.com')) {

        $stmt = $db->prepare(
            "SELECT password , manager_id , username FROM managers WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            $_SESSION['error'] = "Invalid email";
            header("Location: index.php");
            exit();
        }

        $stmt->bind_result($dbPassword,$ID,$username);
        $stmt->fetch();
        

        if (password_verify($password, $dbPassword)) { // use password_verify if hashed
            session_regenerate_id(true);
            $_SESSION['managerEmail'] = $email;
            $_SESSION["managerId"] = $ID;
            $_SESSION["managerUsername"] = $username;
            header("Location: manager.html");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
            header("Location: index.php");
            exit();
        }
    }

    /* =======================
       USER LOGIN
    ======================== */
    $stmt = $db->prepare(
        "SELECT user_id, password, status FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: index.php");
        exit();
    }

    $stmt->bind_result($userId, $dbPassword, $status);
    $stmt->fetch();

    /* ---- STATUS CHECK ---- */
    if ($status === 'suspended') {
        $_SESSION['error'] = "Your account is suspended. Please contact support.";
        header("Location: index.php");
        exit();
    }

    if ($status !== 'active') {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: index.php");
        exit();
    }

    /* ---- PASSWORD CHECK ---- */
    if (!password_verify($password, $dbPassword)) {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: index.php");
        exit();
    }

    /* ---- SUCCESS ---- */
    session_regenerate_id(true);
    $_SESSION['signup_email'] = $email;
    $_SESSION['userID'] = $userId;

    header("Location: Main.html");
    exit();
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