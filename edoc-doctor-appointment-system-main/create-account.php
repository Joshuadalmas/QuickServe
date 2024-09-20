<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload file
require 'vendor/autoload.php';

// Start session and include database connection
session_start();
include("connection.php");

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fname = $_SESSION['personal']['fname'];
    $lname = $_SESSION['personal']['lname'];
    $name = $fname . " " . $lname;
    $address = $_SESSION['personal']['address'];
    $nic = $_SESSION['personal']['nic'];
    $dob = $_SESSION['personal']['dob'];
    $email = mysqli_real_escape_string($database, $_POST['newemail']);
    $tele = mysqli_real_escape_string($database, $_POST['tele']);
    $newpassword = mysqli_real_escape_string($database, $_POST['newpassword']);
    $cpassword = mysqli_real_escape_string($database, $_POST['cpassword']);

    // Validate password strength
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/", $newpassword)) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must be at least 8 characters long and contain at least one letter, one number, and one special character.</label>';
    } elseif ($newpassword != $cpassword) {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Passwords do not match.</label>';
    } else {
        // Check if the email already exists in the database
        $email_check_query = "SELECT * FROM patient WHERE pemail='$email'";
        $email_result = mysqli_query($database, $email_check_query);

        if (mysqli_num_rows($email_result) > 0) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Email already exists.</label>';
        } else {
            // Generate a 6-digit OTP
            $otp = mt_rand(100000, 999999);

            // Store OTP in session variable
            $_SESSION['otp'] = $otp;

            // // Insert user into database with OTP and confirmation status
            // $insert_patient_query = "INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES ('$email', '$name', '$newpassword', '$address', '$nic', '$dob', '$tele')";
            // mysqli_query($database, $insert_patient_query);
            
            // // Insert user into webuser table
            // $insert_webuser_query = "INSERT INTO webuser (email, usertype) VALUES ('$email', 'p')";
            // mysqli_query($database, $insert_webuser_query);

            // Send OTP via email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'joshdalm3@gmail.com'; // Your Gmail address
                $mail->Password = 'xpiacgezbbdnybpa'; // Your Gmail password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('joshdalm3@gmail.com', 'Your Name'); // Sender's email address and name
                $mail->addAddress($email, $name); // Recipient's email address and name
                $mail->isHTML(true);
                $mail->Subject = 'Account Creation OTP';
                $mail->Body = 'Your OTP for account confirmation is: ' . $otp;

                $mail->send();
            } catch (Exception $e) {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</label>';
            }

            $_SESSION["personal2"] = array(
                'newemail' => $email,
                'tele' => $tele,
                'cpassword' => $cpassword,
            );

            // Redirect to OTP verification page
            header('Location: OTPverification.php');
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
    <center>
        <div class="container">
            <table border="0" style="width: 69%;">
                <tr>
                    <td colspan="2">
                        <p class="header-text">Let's Get Started</p>
                        <p class="sub-text">It's Okey, Now Create User Account.</p>
                    </td>
                </tr>
                <form action="" method="POST" >
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="newemail" class="form-label">Email: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="tele" class="form-label">Mobile Number: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="tel" name="tele" class="input-text"  placeholder="ex: 0712345678" pattern="[0]{1}[0-9]{9}" >
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="newpassword" class="form-label">Create New Password: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="cpassword" class="form-label">Conform Password: </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="password" name="cpassword" class="input-text" placeholder="Conform Password" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php echo $error ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                        </td>
                        <td>
                            <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <br>
                            <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                            <a href="login.php" class="hover-link1 non-style-link">Login</a>
                            <br><br><br>
                        </td>
                    </tr>
                </form>
            </table>
        </div>
    </center>
</body>
</html>
