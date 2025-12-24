<?php
session_start();

$db = new mysqli('localhost','root','','wallet_db');
if ($db->connect_error) {
    die("Database Connection Failed");
}

$query = "SELECT Email, status FROM users WHERE Email = ?";

$message = '';
$show_verification_form = false;
$email_error = false;

/* =====================
   EMAIL SUBMIT
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $email = trim($_POST['Email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format';
        $email_error = true;
    } else {

        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {

            $code = random_int(100000, 999999);
            $_SESSION['signup_email'] = $email;
            $_SESSION['signup_code'] = (string)$code;
            $_SESSION['signup_verified'] = false;
            $_SESSION['code_generated'] = true;

            $message = "Verification code sent to $email (code: $code)";
            $show_verification_form = true;

        } else {

            $stmt->bind_result($dbEmail, $status);
            $stmt->fetch();

            if ($status === 'deleted') {
                $message = 'This account was deleted. Please contact support.';
            } else {
                $message = 'Email already exists';
            }

            $email_error = true;
        }

        $stmt->close();
    }
}

/* =====================
   SHOW CODE FORM
===================== */
if (isset($_SESSION['code_generated']) && $_SESSION['code_generated']) {
    $show_verification_form = true;
}

/* =====================
   VERIFY CODE
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_submit'])) {

    $entered = trim($_POST['VerCode'] ?? '');

    if (!empty($_SESSION['signup_code']) && $entered === $_SESSION['signup_code']) {
        $_SESSION['signup_verified'] = true;
        unset($_SESSION['code_generated']);
        header('Location: SIGNUP-2.php');
        exit;
    } else {
        $message = 'Incorrect verification code';
        $show_verification_form = true;
    }
}

$db->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="SIGNUP_stylesheet.CSS">
    <title>SIGN-UP — Verify Email</title>
</head>
    <body style="margin: 0%;">
        <div class="wrapper">
            <div id="page1" class="SIGN_IN_BOX">
                <form class="SIGN_IN_FORM" method="post" action="SIGNUP-1.php">
                    <h3 class="LOGIN">SIGN UP</h3>

                    <h4>We will send a verification code to your email. Please check your inbox.</h4>
                    <?php if ($message): ?>
                        <div style="color: #333; padding: 8px;"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <div class="row_inputs">
                        <div class="input-1"><div class="About_Input">Email</div>
                            <input id="Email" type="email" class="info_inputs" required placeholder="email@example.com" name="Email" value="<?php echo htmlspecialchars($_SESSION['signup_email'] ?? ''); ?>" <?php echo $email_error ? 'style="border: 2px solid red;"' : ''; ?>>
                        </div>
                    </div>

                    <hr style="width:100% ; color:black;">
                    <div class="row_inputs">
                        <button class="Next_button"> <a style="text-decoration: none; color: black; font-size: 100%;" href="SIGNUP.html">Return</a></button>
                        <input class="Next_button" type="submit" name="submit" value="Send Code">
                    </div>
                </form>

                <!-- Verification form (only shown after successful code generation) -->
                <?php if ($show_verification_form): ?>
                <form class="SIGN_IN_FORM" method="post" action="SIGNUP-1.php" style="margin-top:16px;">
                    <div class="row_inputs">
                        <div class="input-1"><div class="About_Input">Verification Code</div>
                            <input id="code" type="text" class="info_inputs" required placeholder="#" name="VerCode">
                        </div>
                    </div>
                    <div class="row_inputs">
                        <input class="Next_button" type="submit" name="verify_submit" value="Verify Code">
                    </div>
                </form>
                <?php endif; ?>

            </div>
        </div>

        <script src="SIGNUP.js"></script>
    </body>
</html>