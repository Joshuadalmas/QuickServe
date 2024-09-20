<?php
session_start();

// Check if OTP session variable is set
if (!isset($_SESSION['otp'])) {
    // Handle case where OTP session variable is not set
    echo "OTP verification failed. Please try again.";
    exit();
}

// Define an error message variable
$error_message = "";

// Check if user submitted the form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve user input OTP
    $user_otp = $_POST['otp'];

    // Retrieve OTP from session
    $stored_otp = $_SESSION['otp'];

    // Check if user input matches stored OTP
    if ($user_otp == $stored_otp) {
        // OTP verification successful

        // Redirect user to respective dashboard based on user type
        if (isset($_SESSION['usertype'])) {
            $usertype = $_SESSION['usertype'];
            switch ($usertype) {
                case 'a':
                    // Admin dashboard
                    header('Location: admin/index.php');
                    break;
                case 'p':
                    // Patient dashboard
                    header('Location: patient/index.php');
                    break;
                case 'd':
                    // Doctor dashboard
                    header('Location: doctor/index.php');
                    break;
                default:
                    // Invalid user type
                    $error_message = "Invalid user type.";
            }
        } else {
            // User type not set in session
            $error_message = "User type not specified.";
        }
    } else {
        // Handle case where OTP verification fails
        $error_message = "Invalid OTP. Please try again.";
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
    <link rel="stylesheet" href="css/login.css">

    <title>OTP Verification</title>
</head>
<body>
    <center>
        <div class="container">
            <table border="0" style="margin: 0;padding: 0;width: 60%;">
                <tr>
                    <td>
                        <p class="header-text">OTP Verification</p>
                    </td>
                </tr>
                <div class="form-body">
                    <tr>
                        <td>
                            <p class="sub-text">Almost there, enter the OTP sent to your email</p>
                        </td>
                    </tr>
                    <tr>
                        <form action="" method="POST" >
                            <td class="label-td">
                                <label for="otp" class="form-label">Enter OTP:</label>
                            </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <input type="text" id="otp" name="otp" class="input-text" placeholder="6-digit OTP" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" name="submit" value="Verify OTP" class="login-btn btn-primary btn">
                        </td>
                    </tr>
                    <?php
                    // Display error message if it's set
                    if (!empty($error_message)) {
                        echo '<tr><td><p style="color: red;">' . $error_message . '</p></td></tr>';
                    }
                    ?>
                </form>
                <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Not working&#63; Retry the </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <label for="" class="sub-text" style="font-weight: 280;"> page</label>
                    <br><br><br>
                </td>
            </tr>
                </div>
            </table>
        </div>
    </center>
</body>
</html>
