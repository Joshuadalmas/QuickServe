<?php

    $database= new mysqli("localhost","xxxx","xxxx","xxxx");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

?>
