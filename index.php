<?php 
$message="";
if(isset($_POST['LOGIN'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    if(empty($email) || empty($password)){
        $message = "Email and password are required";
    }else{
        if($email == "majdmaatouk@gmail.com" && $password == "12345678"){
            header("Location: Main.html");
            exit();
            
        }else{
            $message="Invalid email or password";
        }
    }
}
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
            <input id="email" placeholder="Email" type="email" required="required" class="input-box" name="email" oninput="resetMessage()">
            <input id="password" placeholder="Password" type="password" required="required" class="input-box" oninput="show_icon(); resetMessage();" name="password">
            <span class="show-password" onclick="Show_password()"></span>
            <input type="submit" name="LOGIN"  class="SIGN_IN_BUTTON" value="LOGIN" >
            
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