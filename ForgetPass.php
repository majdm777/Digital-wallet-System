<?php 
$message = "";
$success_message = "";

if(isset($_POST['send'])){
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if(empty($email)){
        $message = "Email is required";
    } else {
        // Validate email format
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $message = "Invalid email format";
        } else {
            // Check if email exists (replace with your actual validation)
            if($email == "majdmaatouk@gmail.com" || $email == "majdmaatouk@email.com"){
                // Email exists - send reset link or show success message
                // TODO: Implement actual password reset email sending here
                $success_message = "Password reset link has been sent to your email";
            } else {
                $message = "Email not found in our system";
            }
        }
    }
}
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ForgetPass.css">
    <title>LOG-IN page</title>
</head>
<body>
    <div class="wrapper">
        
    <div class="SIGN_IN_BOX">
        <h3 class="LOGIN">Please enter your email to recover your Password</h3>
        <?php if (!empty($message)): ?>
            <div class="message error-message" style="color: red; margin: 10px 0;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message success-message" id="success-indicator" style="color: green; margin: 10px 0;"><?php echo htmlspecialchars($success_message); ?></div>
            <script>
                // Flag to indicate successful submission
                var sendSuccess = true;
            </script>
        <?php endif; ?>
        <form class="SIGN_IN_FORM" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">  
            <input id="email" name="email" placeholder="Email" type="email" required="required" class="input-box" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">           
            <input type="submit" name="send" class="SIGN_IN_BUTTON" id="request-button" value="SEND" >
        </form>
       
        
        <div class="OTHER_OPTIONS">
            <button class="SIGN_IN_BUTTON"><a style="text-decoration: none; color: grey;" href="index.php">GO BACK</a></button>        
            <div class="message" id="request-message"></div>
            <div class="timer" id="countdown"></div>
        </div>

    </div>
    
    </div>

    <script src="ForgetPass.js"></script>
</body>
</html>