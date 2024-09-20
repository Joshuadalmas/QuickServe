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
if($_GET){
    $action=$_GET["action"];
    if($action=='book-appointment'){
include("../connection.php");
$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["pid"];
$useremail= $userfetch["pemail"];

// Check if PayPal has returned the payment ID and Payer ID
if (isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
    // PayPal transaction details
    $payment_id = $_GET['paymentId'];
    $payer_id = $_GET['PayerID'];

    // Your PayPal API credentials
    $client_id = 'XXXX_XXXX_XXXX';
    $client_secret = 'XXXX_XXXX';

    // Set up PayPal API endpoint
    $api_endpoint = 'https://api.sandbox.paypal.com'; // Use sandbox endpoint for testing

    // Get access token
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

    // Decode access token response
    $response_data = json_decode($response, true);

    // Check if decoding was successful and if access token exists
    if (is_array($response_data) && isset($response_data['access_token'])) {
        $access_token = $response_data['access_token'];

        // Execute payment
        $execution_data = array(
            'payer_id' => $payer_id
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint . '/v1/payments/payment/' . $payment_id . '/execute');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($execution_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        // Decode payment execution response
        $response_data = json_decode($response, true);

        // Check if decoding was successful
        if (is_array($response_data)) {
            // Check if payment is successful
            if (isset($response_data['state']) && $response_data['state'] == 'approved') {
                // Payment successful
                $transaction_id = isset($response_data['id']) ? $response_data['id'] : "Unknown";

                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "Samlad-123";
                $dbname = "edoc";

                try {
                    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Get appointment details
                    $appointment_id = 2; // Replace with actual appointment ID
                    $stmt = $pdo->prepare("SELECT pid FROM appointment WHERE appoid = :appoid");
                    $stmt->bindParam(':appoid', $appointment_id);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);


                    // Insert payment data into payments table
                    $stmt = $pdo->prepare("INSERT INTO payments (pid, pemail, paypal_order_id) VALUES (:pid, :pemail, :paypal_order_id)");
                    $stmt->bindParam(':pid', $userid);
                    $stmt->bindParam(':pemail', $useremail);
                    $stmt->bindParam(':paypal_order_id', $transaction_id);

                    $stmt->execute();

                    
                        $apponum=$_GET["apponum"];
                        $scheduleid=$_GET["scheduleid"];
                        $date=$_GET["date"];
                        
                        
                        
                            
                            // Insert the appointment into the database
                            $sql2="INSERT INTO appointment(pid, apponum, scheduleid, appodate) 
                            VALUES ($userid, $apponum, $scheduleid, '$date')";
                            $result= $database->query($sql2);
                            header("location: appointment.php?action=booking-added&id=".$apponum."&titleget=none");
                        exit();
                    


                       
                    
                    
                } catch(PDOException $e) {
                    echo "<h1>Payment Failed</h1>";
                    echo "Error: " . $e->getMessage();
                }
            } else {
                // Payment failed
                echo "<h1>Payment Failed</h1>";
                // Display error message
                if (isset($response_data['message'])) {
                    echo "<p>Error: " . $response_data['message'] . "</p>";
                }
            }
        } else {
            // Failed to decode response data
            echo "<h1>Payment Failed</h1>";
            echo "<p>Error: Failed to process payment. Please try again later.</p>";
        }
    } else {
        // Failed to decode access token response data or access token not found
        echo "<h1>Payment Failed</h1>";
        echo "<p>Error: Failed to process payment. Please try again later.</p>";
    }
} else {
    // Redirect to homepage or error page
    header("Location: /");
    exit;
}
    }
}
?>
