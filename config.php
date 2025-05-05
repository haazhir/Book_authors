<?php
$host = 'localhost';
$dbname = 'hazhir5_AB';
$username = 'hazhir5_library';
$password = 'Libr@ry21';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
