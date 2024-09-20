<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Sign Up</title>
    
</head>
<body>
<?php
session_start();

// Set the new timezone
date_default_timezone_set('Africa/Nairobi');
$date = date('Y-m-d');
$_SESSION["date"] = $date;

// Assume database connection is already established
include("connection.php");

if ($_POST) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $address = $_POST['address'];
    $nic = $_POST['nic'];
    $dob = $_POST['dob'];
    

    // Check if NIC already exists in the database
    $nic_check_query = "SELECT * FROM patient WHERE pnic='$nic'";
    $nic_result = mysqli_query($database, $nic_check_query);

    if (mysqli_num_rows($nic_result) > 0) {
        echo '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">NIC already exists.</label>';
    } else {
        // Check if the user is at least 18 years old
        $birthdate = new DateTime($dob);
        $today = new DateTime();
        $age = $birthdate->diff($today)->y;

        if ($age < 18) {
            echo '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">You must be at least 18 years old to sign up.</label>';
        } else {
            // Store personal details in session
            $_SESSION["personal"] = array(
                'fname' => $fname,
                'lname' => $lname,
                'address' => $address,
                'nic' => $nic,
                'dob' => $dob,
            );

            // Redirect to the next page
            header("location: create-account.php");
            exit();
        }
    }
}
?>

    <center>
    <div class="container">
        <table border="0">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">Add Your Personal Details to Continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="name" class="form-label">Name: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="fname" class="input-text" placeholder="First Name" required>
                </td>
                <td class="label-td">
                    <input type="text" name="lname" class="input-text" placeholder="Last Name" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="address" class="form-label">Address: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <select name="address" class="input-text" required>
                        <option value="" disabled selected>County</option>
                        <?php
                            // Query to fetch counties data from the database
                            $query = "SELECT * FROM counties ORDER BY name ASC";
                            $result = $database->query($query);

                            // Check if query was successful and if there are rows returned
                            if ($result && $result->num_rows > 0) {
                                // Loop through each row of the result set
                                while ($row = $result->fetch_assoc()) {
                                    // Output each county as an option
                                    echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                }
                                // Free the result set
                                $result->free();
                            } else {
                                // Display a default option if no counties are available
                                echo '<option value="" disabled>No counties available</option>';
                            }
                        ?>
                    </select>    
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="nic" class="form-label">NIC: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="nic" class="input-text" placeholder="NIC Number" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="dob" class="form-label">Date of Birth: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="date" name="dob" class="input-text" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                </td>
            </tr>

            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Next" class="login-btn btn-primary btn">
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
            </tr>
        </table>

    </div>
</center>
</body>
</html>