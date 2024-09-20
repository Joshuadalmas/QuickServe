<?php

    $database= new mysqli("localhost","root","Samlad-123","edoc");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

?>