<?php
$host = 'localhost';
$dbname = 'university_management_system';
$username = 'root'; // use your database username
$password = 'quest4inno@server'; // use your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>
