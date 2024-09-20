<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
        
    <title>Login</title>
</head>
<body>
<?php
// Start the session
session_start();

// Unset all the server side variables
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$_SESSION["date"] = $date;

// Import database
include("connection.php");

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-20240124T112451Z-001/PHPMailer/src/Exception.php';
require 'PHPMailer-20240124T112451Z-001/PHPMailer/src/PHPMailer.php';
require 'PHPMailer-20240124T112451Z-001/PHPMailer/src/SMTP.php';

if ($_POST) {
    $email = $_POST['useremail'];
    $password = $_POST['userpassword'];
    
    $error = '<label for="promter" class="form-label"></label>';

    $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
    if ($result->num_rows == 1) {
        $utype = $result->fetch_assoc()['usertype'];
        if ($utype == 'p') {
            // For patients
            $checker = $database->query("SELECT * FROM patient WHERE pemail='$email' AND BINARY ppassword='$password'");
            if ($checker->num_rows == 1) {
                // Handle patient login
                handleLogin($email, 'p');
            } else {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
            }
        } elseif ($utype == 'd') {
            // For doctors
            $checker = $database->query("SELECT * FROM doctor WHERE docemail='$email' AND BINARY docpassword='$password'");
            if ($checker->num_rows == 1) {
                // Handle doctor login
                handleLogin($email, 'd');
            } else {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
            }
        } elseif ($utype == 'a') {
            // For admins
            $checker = $database->query("SELECT * FROM admin WHERE aemail='$email' AND BINARY apassword='$password'");
            if ($checker->num_rows == 1) {
                // Handle admin login
                handleLogin($email, 'a');
            } else {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
            }
        } else {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid user type</label>';
        }
    } else {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We can\'t find any account for this email</label>';
    }
} else {
    $error = '<label for="promter" class="form-label">&nbsp;</label>';
}

function handleLogin($email, $userType) {
    global $database;
    global $_SESSION;
    
    // Generate a 6-digit OTP
    $otp = rand(100000, 999999);

    // Set OTP expiry time (e.g., 5 minutes from now)
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Update OTP and OTP expiry in the database
    $update_query = "UPDATE webuser SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'";
    $database->query($update_query);

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'joshdalm3@gmail.com'; // Your email
    $mail->Password = 'xpiacgezbbdnybpa'; // Your email password
    $mail->Port = 465;
    $mail->SMTPSecure = 'ssl';
    $mail->isHTML(true);
    $mail->setFrom('joshdalm3@gmail.com', 'Doctor\'s appointment payment system');
    $mail->addAddress($email);
    $mail->Subject = 'DAS OTP';
    $mail->Body = 'Your OTP is: ' . $otp;
    $mail->send();

    // Store OTP in session
    $_SESSION['otp'] = $otp;
    $_SESSION['user'] = $email;
    $_SESSION['usertype'] = $userType;
    
    // Redirect to OTP verification page
    header('location: OTP_verification.php');
    exit();
}
?>


    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
            <label for="" class="sub-text" style="font-weight: 280;">Forgot password&#63; </label>
                    <a href="forgot_password.php" class="hover-link1 non-style-link">Click Here</a>
                    <br><br><br>
                </td>
            </tr>
                        
                    </form>
        </table>

    </div>
</center>
</body>
</html>
