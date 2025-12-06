<?php
session_start();

try {
    $db = new mysqli('localhost','root','','wallet_tester');
} catch (\PDOException $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>"); 
}

$query1="SELECT Email FROM user WHERE Email=?";


// Simple target email to check against. Replace or extend as needed.
$TARGET_EMAIL = 'majdmaatouk@gmail.com';

$message = '';

// Handle email submission from SIGNUP.html
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $email = trim($_POST['Email'] ?? '');
    $stmt=$db->prepare($query1);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows == 0) {
        // generate a 6-digit code and store it in session
        $code = random_int(100000, 999999);
        $_SESSION['signup_email'] = $email;
        $_SESSION['signup_code'] = (string)$code;
        $_SESSION['signup_verified'] = false;

        // try to send email (may not work on local dev)
        $subject = 'Your verification code';
        $body = "Your verification code is: $code";
        // mail() may be disabled locally; ignore failure for now
        // @mail($email, $subject, $body);

        // For local testing we also display the code on-screen.
        $message = 'Verification code sent to ' . htmlspecialchars($email) . '. Please check your email and enter the code below. (code: ' . htmlspecialchars($code) . ')';
    } else {
        $message = 'Email already exists. Please use another email.';
    }
}

// Handle verification form submit (code entry)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_submit'])) {
    $entered = trim($_POST['VerCode'] ?? '');
    if (!empty($_SESSION['signup_code']) && $entered === $_SESSION['signup_code']) {
        $_SESSION['signup_verified'] = true;
        // redirect to details page
        header('Location: SIGNUP-2.php');
        exit;
    } else {
        $message = 'Verification code incorrect. Please try again.';
    }
}
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
                            <input id="Email" type="email" class="info_inputs" required placeholder="email@example.com" name="Email" value="<?php echo htmlspecialchars($_SESSION['signup_email'] ?? ''); ?>">
                        </div>
                    </div>

                    <hr style="width:100% ; color:black;">
                    <div class="row_inputs">
                        <button class="Next_button"> <a style="text-decoration: none; color: black; font-size: 100%;" href="SIGNUP.html">Return</a></button>
                        <input class="Next_button" type="submit" name="submit" value="Send Code">
                    </div>
                </form>

                <!-- Verification form -->
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

            </div>
        </div>

        <script src="SIGNUP.js"></script>
    </body>
</html>