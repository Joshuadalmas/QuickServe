<?php

session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }

}else{
    header("location: ../login.php");
}


// Import database
include("../connection.php");
$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["pid"];
$username=$userfetch["pname"];
$patientData = $database->query("SELECT cancel_count FROM patient WHERE pid = '$userid'");
$patientRow = $patientData->fetch_assoc();
$cancelCount = $patientRow['cancel_count'];


// Get the last cancel timestamp and cancel count from the patient table
$sql_last_cancel = "SELECT last_cancel_timestamp, cancel_count FROM patient WHERE pid = '$userid'";
$result_last_cancel = $database->query($sql_last_cancel);
$row_last_cancel = $result_last_cancel->fetch_assoc();
$last_cancel_timestamp = $row_last_cancel['last_cancel_timestamp'];
$cancel_count = $row_last_cancel['cancel_count'];

// Calculate the difference between the last cancel timestamp and the current time
$current_time = time();
$time_difference = $current_time - strtotime($last_cancel_timestamp);

// If the time difference is more than 86400 seconds (24 hours) and cancel count is more than 1, reset cancel count to 0
if ($time_difference > 86400 && $cancel_count > 1) {
    $sql_reset_cancel_count = "UPDATE patient SET cancel_count = 0 WHERE pid = '$userid'";
    if ($database->query($sql_reset_cancel_count)) {
        
    } else {
        // Error occurred while resetting cancel count
        echo "Error occurred while resetting cancel count.";
    }
}



// Check if the patient already has an appointment
$sql_check_appointment = "SELECT * FROM appointment WHERE pid = $userid";
$result_check_appointment = $database->query($sql_check_appointment);

$out_result=$_POST["out_result"];


// Your ExchangeRate-API access key
$access_key = '3ebf3ec82774fcda803133f3';

// Base currency and target currency
$base_currency = 'USD'; 
$target_currency = 'KES'; 

// Amount to convert
$amount = $out_result; // Example amount to convert

// Construct API URL
$url = "https://api.exchangerate-api.com/v4/latest/$base_currency";

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    // Handle cURL error
    echo 'Error: ' . curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Check if response is valid
if ($data && isset($data['rates'][$target_currency])) {
    // Get exchange rate for target currency
    $exchange_rate = $data['rates'][$target_currency];

    // Perform currency conversion
    $converted_amount = $amount / $exchange_rate;


} else {
    // Handle API error or invalid response
    header("Location /appointment.php");
}






            
// Check if the user has canceled appointments more than twice
if ($cancelCount >= 2) {
    // If the cancel count is greater than or equal to 2, display a message and prevent booking
    echo '
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/animations.css">  
        <link rel="stylesheet" href="../css/main.css">  
        <link rel="stylesheet" href="../css/admin.css">

        <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br>
                            <h2>Booking Warning!</h2>
                            <a class="close" href="index.php">&times;</a>
                            <div class="content">
                            You are not allowed to book appointments after canceling more than once a day. Incase this is an error, try again.<br><br>
                                
                            </div>
                            <div style="display: flex;justify-content: center;">
                            
                            <a href="index.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                            <br><br><br><br>
                            </div>
                        </center>
                </div>
                </div>
                ';
}else{
    if($result_check_appointment->num_rows > 0) {
        // Patient already has an appointment, show error popup
        echo '
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/animations.css">  
        <link rel="stylesheet" href="../css/main.css">  
        <link rel="stylesheet" href="../css/admin.css">

        <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br>
                            <h2>Sorry.</h2>
                            <a class="close" href="appointment.php">&times;</a>
                            <div class="content">
                            Your can only book one appointment per session<br><br>
                                
                            </div>
                            <div style="display: flex;justify-content: center;">
                            
                            <a href="appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                            <br><br><br><br>
                            </div>
                        </center>
                </div>
                </div>
                ';
    } else {
        if($_POST && isset($_POST["booknow"])) {
            $apponum=$_POST["apponum"];
            $scheduleid=$_POST["scheduleid"];
            $date=$_POST["date"];
            
            
            

            // Check the number of bookings for the specified session
            $sql_count_bookings = "SELECT COUNT(*) AS num_bookings FROM appointment WHERE scheduleid = $scheduleid";
            $result_count_bookings = $database->query($sql_count_bookings);
            $row_count_bookings = $result_count_bookings->fetch_assoc();
            $num_bookings = $row_count_bookings['num_bookings'];

            // Get the maximum number of bookings allowed for the session
            $sql_get_max_bookings = "SELECT nop FROM schedule WHERE scheduleid = $scheduleid";
            $result_max_bookings = $database->query($sql_get_max_bookings);
            $row_max_bookings = $result_max_bookings->fetch_assoc();
            $max_bookings = $row_max_bookings['nop'];

            if ($num_bookings >= $max_bookings) {
                // Schedule already fully booked, show error popup
                echo '
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="stylesheet" href="../css/animations.css">  
                    <link rel="stylesheet" href="../css/main.css">  
                    <link rel="stylesheet" href="../css/admin.css">

                    <style>
                    .popup{
                        animation: transitionIn-Y-bottom 0.5s;
                    }
                    .sub-table{
                        animation: transitionIn-Y-bottom 0.5s;
                    }
                    </style>
                    
                    <div id="popup1" class="overlay">
                        <div class="popup">
                            <center>
                                <br><br>
                                <h2>Sorry.</h2>
                                <a class="close" href="appointment.php">&times;</a>
                                <div class="content">
                                    This session has reached its maximum bookings limit.
                                </div>
                                <div style="display: flex;justify-content: center;">
                                    <a href="appointment.php" class="non-style-link">
                                        <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                            <font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font>
                                        </button>
                                    </a>
                                    <br><br><br><br>
                                </div>
                            </center>
                        </div>
                    </div>
                ';
            }  else {
                // If schedule is not booked, show confirmation popup before booking
                echo '
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="../css/animations.css">  
                <link rel="stylesheet" href="../css/main.css">  
                <link rel="stylesheet" href="../css/admin.css">

                <style>
                .popup{
                    animation: transitionIn-Y-bottom 0.5s;
                }
                .sub-table{
                    animation: transitionIn-Y-bottom 0.5s;
                }
                </style>

                <div id="popup1" class="overlay">
                    <div class="popup">
                        <center>
                            <br><br>
                            <h2>Confirmation</h2>
                            <div class="content">
                                Are you sure you want to book this appointment?
                            </div>
                            <div style="display: flex;justify-content: center;">
                                <form id="bookingForm" action="" method="post">
                                    <input type="hidden" name="apponum" value="' . $apponum . '">
                                    <input type="hidden" name="scheduleid" value="' . $scheduleid . '">
                                    <input type="hidden" name="date" value="' . $date . '">
                                    <input type="hidden" name="out_result" value="' . $out_result . '">
                                    <button type="submit" name="confirm_booking" class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                        <font class="tn-in-text">&nbsp;&nbsp;Yes&nbsp;&nbsp;</font>
                                    </button>
                                </form>
                                <a href="appointment.php" class="non-style-link">
                                    <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                        <font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font>
                                    </button>
                                </a>
                            </div>
                        </center>
                    </div>
                </div>
                ';

                // Add a hidden form to submit on confirmation
                echo '
                <form id="bookingForm" action="" method="post" style="display: none;">
                    <input type="hidden" name="apponum" value="' . $apponum . '">
                    <input type="hidden" name="scheduleid" value="' . $scheduleid . '">
                    <input type="hidden" name="date" value="' . $date . '">
                    <input type="hidden" name="out_result" value="' . $out_result . '">
                    <input type="hidden" name="confirm_booking" value="true">
                </form>
                ';
            }
        }
    }





        // Booking confirmation handling
    if($_POST && isset($_POST["confirm_booking"])) {
        $apponum=$_POST["apponum"];
        $scheduleid=$_POST["scheduleid"];
        $date=$_POST["date"];
        


        // header("location: booking-complete2.php?action=book-appointment&apponum=".$apponum."&scheduleid=".$scheduleid."&date=".$date."");

            // Replace 'YOUR_CLIENT_ID' and 'YOUR_CLIENT_SECRET' with your PayPal API credentials
        $client_id = 'Ac0ExpFnWr_8M8vSApvr4lk_uytxFUgd4jtaOf6NPLruw7BuIK8doqhuof_uOpRwNDUsxOFXkVN3ZDTm';
        $client_secret = 'ENgOgrujOLyPZHNfk92DsRhhGMaQ-ODo55uRaJK6TDCdPmvYT_sbcFRccHvTzVgMUmcKi-sAubMkZyHl';

        // Sample item details (modify as needed)
        $item_name = "Sample Item";
        $item_price = number_format(floatval($converted_amount), 2);

        // Set up PayPal API endpoint
        $api_endpoint = 'https://api.sandbox.paypal.com';

        // Set up PayPal REST API authentication
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        );

        $data = 'grant_type=client_credentials';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);

        $access_token = $response_data['access_token'];

        // Create PayPal payment
        $payment_data = array(
            'intent' => 'sale',
            'payer' => array(
                'payment_method' => 'paypal'
            ),
            'transactions' => array(
                array(
                    'amount' => array(
                        'total' => $item_price,
                        'currency' => 'USD' // Changed currency to USD
                    ),
                    'description' => $item_name
                )
            ),
            'redirect_urls' => array(
                'return_url' => 'http://localhost:3000/edoc-doctor-appointment-system-main/edoc-doctor-appointment-system-main/patient/booking-complete2.php?action=book-appointment&apponum=' . $apponum . '&scheduleid=' . $scheduleid . '&date=' . $date, // Replace with your success page URL
                'cancel_url' => 'http://localhost:3000/edoc-doctor-appointment-system-main/edoc-doctor-appointment-system-main/patient/appointment.php' // Replace with your cancel page URL
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint . '/v1/payments/payment');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);

        // Check if payment creation was successful
        if (isset($response_data['links'][1]['href'])) {

            $approval_url = $response_data['links'][1]['href']; // Redirect user to this URL to complete payment
            header("Location: $approval_url");
            exit;
        } else {
            // Handle payment creation failure
            echo "Payment creation failed. Please try again later.";
        }

    }
}

?>

