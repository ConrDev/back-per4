<?php
session_start();
if (isset($_SESSION['errors'])) {
  unset($_SESSION['errors']);
}
require_once '../config/config.inc.php';

$id = $_GET['id'];

$sql = "DELETE FROM `leden` WHERE `id`= '$id'";
$result = $mysqli->query($sql);
if ($result) {
    header('Location: ../../views/inschrijvingen.php');
    exit;
} else {
    header('Location: ../../views/inschrijvingen.php');
    $_SESSION['errors']['verwijder-error'] = "Er ging wat mis met het verwijderen!";
    exit;
}
