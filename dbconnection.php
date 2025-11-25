<?php
$servername = "localhost";
$username = "root";
$password = ""; // Empty password for XAMPP/WAMP
// $password = "root"; // Uncomment this if you're using MAMP

// First, connect without selecting database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to MySQL server successfully!<br>";

// Now check if database exists
$dbname = "wemotions";
if (!$conn->select_db($dbname)) {
    echo "Database '$dbname' does not exist. Creating it...<br>";
    
    // Create the database
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
    } else {
        echo "Error creating database: " . $conn->error . "<br>";
    }
} else {
    echo "Database '$dbname' selected successfully!<br>";
}

$conn->close();
?>