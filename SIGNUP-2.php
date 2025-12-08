<?php
session_start();

// Require that the user completed verification first
if (empty($_SESSION['signup_verified']) || $_SESSION['signup_verified'] !== true) {
    // Not verified — send back to verification page
    header('Location: SIGNUP-1.php');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="SIGNUP-2.CSS">
</head>
<body style="margin:0%">
    <div class="wrapper">
    <div id="page1" class="SIGN_IN_BOX">
        <form class="SIGN_IN_FORM" method="post" action="signup_process.php">
            <h3 class="LOGIN">SIGN UP</h3>
            <?php 
                if(isset($_GET['submit2'])){
                    $email=$_GET['Email-Hidden'];
                    $code=$_GET['Code-Hidden'];
                    $vercode=$_GET['VerCode'];
                    if($code!=$vercode){ //if code doesnt match
                        exit();
                    }
                }
                // include the verified email as hidden field for the next step
                $email = $_SESSION['signup_email'] ?? '';
                echo '<input type="hidden" name="Email-Hidden" value="' . htmlspecialchars($email) . '">';
            ?>
            

            <div class="row_inputs">
                <div class="input"><div class="About_Input">First-Name</div>  <input  id="First-Name" type="text" class="info_inputs" required max="10" name="First-Name"></div>
                <div class="input"><div class="About_Input">Last-Name</div> <input  id="Last-Name" type="text" class="info_inputs" required maxlength="10" name="Last-Name"></div>
            </div>

            <div class="row_inputs">
                <div class="input"><div class="About_Input">Date-Of-Birth</div>  <input  id="Date-Of-Birth" type="date" class="info_inputs"  name="Date-Of-Birth"></div>
                <div class="input"><div class="About_Input">Nationality</div> <input  id="Nationality" type="text" class="info_inputs" required  name="Nationality"></div>
            </div>

            <div class="row_inputs">
                <div class="input"><div class="About_Input">Password</div>  <input  id="password" type="password" class="info_inputs" required max="10" name="Passwaord"></div>
                <div class="input"><div class="About_Input">Confirm Password</div> <input  id="con_password" type="password" class="info_inputs" required maxlength="10"></div>
            </div>


            <div class="row_inputs">
                <div class="input"><div class="About_Input">Address</div>  <input  id="address" type="text" class="info_inputs" required maxlength="10" placeholder="beirut" name="Address"></div>
                <div class="input"><div class="About_Input">Income-Source</div>
                     <select id="Income-Source" class="info_inputs" required name="Income-Source">
                        <option value="" class="info_inputs" disabled selected></option>
                        <option value="Employment" id="select-value"class="info_inputs">Employment</option>
                        <option value="Investments" id="select-value"  class="info_inputs">Investments</option>
                        <option value="Retirement" id="select-value" class="info_inputs">Retirement</option>
                        <option value="Enterpreneurship" id="select-value" class="info_inputs">Enterpreneurship</option>
                        <option value="Rental-Income" id="select-value" class="info_inputs">Rental Income</option>
                        <option value="Government-benefits" id="select-value" class="info_inputs">Government benefits</option>
                        <option value="Other-sources" id="select-value" class="info_inputs">Other sources</option>

                    </select> 
                </div>
            </div>

            <div class="row_inputs">
                <div class="input"><div class="About_Input">Type-Of-Account</div>
                <select id="type-of-account" class="info_inputs" required name="Type-Of-Account">
                    <option value="" class="info_inputs" disabled selected></option>
                    <option value="checking" id="select-value" class="info_inputs">checking</option>
                    <option value="savings" id="select-value" class="info_inputs">savings</option>
                    <option value="business" id="select-value" class="info_inputs">buisness</option>          
                </select>
            </div>
                <div class="input"><div class="About_Input" >phone</div>
                    <input id="phone"class="info_inputs" required name="Phone" type="tel">

                </div>
            </div>

           
            
            <div class="row_inputs">
            <input  id="terms" type="checkbox" class="checkbox" name="terms"><a class="terms" href="" style="color: black;">Our Terms</a>           
            </div>     
            <hr style="width: 100%; color:black ;">
            <div class="row_inputs"> 
                <input class="Next_button" id="signedup" type="submit" name="submit3" value="Done"> 
            </div>   


           

        </form>
        <div class="row_inputs">    
                <button class="Next_button"> <a style="text-decoration: none; color: black; font-size: 100%;" href="SIGNUP.html">Return</a></button>
                <button class="Next_button" id="Next_button" onclick="LastPage()">Next</button>
                
        </div>


        
        
        <!-- <div class="terms">
            <input  id="terms" type="checkbox" class="checkbox"><a class="terms" href="" style="color: black;">Our Terms</a>
        </div> -->
        <!-- <div class="OTHER_OPTIONS">
            <button class="Next_button" onclick="Back()">BACK</button>
            <button class="Next_button" id="Next_button" onclick="LastPage()">NEXT</button>
        </div> -->
        <div class="message" id="display-message-2"></div>
    </div>
    
    
    </div>

    <script src="SIGNUP.js"></script>
</body>
</html>