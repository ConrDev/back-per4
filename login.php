<?php
session_start();
if (isset($_SESSION['errors'])) {
  unset($_SESSION['errors']);
}
require_once 'backend/config/config.inc.php';

$username = $_POST['username'];
$password = $_POST['password'];

if (strlen($username) > 0 && strlen($password) > 0) {
  $password = md5($password);

  // $query = "SELECT * FROM `accounts` WHERE `username`=':username' AND `password`=':password'";
  $sql = "SELECT * FROM `accounts` WHERE `username`='$username' AND `password`='$password'";
  $result = $mysqli->query($sql);

  // if ($stmt = $mysqli->prepare($query)) {

  //   $values = [
  //     ":username" => $username,
  //     ":password" => $username,
  //   ];
  // } if ($stmt->execute($values)) {
    // $query = "SELECT * FROM `accounts` WHERE `username`='$username' AND `password`='$password'";

    // $result = mysqli_query($mysqli, $query);

  if ($result->fetch()) {
    session_start();

    $_SESSION['loggedin'] = true;

    header('location: ./index.php');
  } else {
    header('location: ./views/login.php');
    $_SESSION['errors']['login-error'] = "Er is een fout opgetreden!";
    exit;
  }
} else {
  header('location: ./views/login.php');
  $_SESSION['errors']['login-error'] = "Niet alle velden zijn ingevuld!";
  exit;
}


?>