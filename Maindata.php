<?php
try {
    $db = new mysqli('localhost', 'root', '', 'wallet_db');
} catch (\Exception $e) {
    // Die with a connection error message
    die("<h1>Database Connection Failed!</h1><p>Error: " . $e->getMessage() . "</p>");
}




?>