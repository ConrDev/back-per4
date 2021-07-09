<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Dit zijn de MYSQL inloggegevens.
$host       = 'localhost';
$username   = 'root';
$password   = '';
$database   = 'back4';

try {
    $mysqli = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
} catch (PDOException $e) {
    die($e->getMessage());
}

?>
