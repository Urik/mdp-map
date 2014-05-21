<?php

define('HOST', 'javiercasa.no-ip.info');
define('USER', 'tesis');
define('PASSWORD', 'tesis123');
define('DATABASE', 'tesis');

function connectDB() {

    $con = new mysqli(HOST, USER, PASSWORD, DATABASE);
    if ($con->connect_error) {
        die('Connect Error (' . $con->connect_errno . ') '
                . $con->connect_error);
    }
    return $con;
    
}

function disconnectDB($con) {
    $con->close();
}
?>

