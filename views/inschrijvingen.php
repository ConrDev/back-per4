<?php
session_start();

include '../backend/config/config.inc.php';

$sql = "SELECT * FROM `leden`";
$result = $mysqli->query($sql);

// $inschrijvingen = $data->fetchColumn();

// var_dump($_SESSION['errors']);

?>

<!doctype html>
<html lang="en">

<head>
    <title>Back 4</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<style>
    body {
        background-image: url("../assets/Glr.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .bg {
        width: 100%;
        background-image: url("../assets/Glr.jpg");
        filter: blur(4px);
        -webkit-filter: blur(4px);
        height: 100%;
        background-position-y: 75%;
        background-repeat: no-repeat;
        background-size: cover;
        position: fixed;
    }
</style>

<body>
    <div class="bg"></div>
    <div class="col-5 mx-auto p-5">
        <img src="../assets/logo.png" alt="GLR" class="mx-auto d-block w-25">
    </div>

    <div class="modal-content col-7 mx-auto p-5">
        <a class="btn btn-info text-center mb-2" style="margin-left: 45rem !important;" href="../index.php">Terug</a>
        <a class="btn btn-info text-center mb-5" style="margin-left: 45rem !important;" href="../logout.php">Loguit</a>

        <small name="emailError" id="emailError" class="form-text text-muted">
            <?php if (!empty($_SESSION['errors']['verwijder-error'])) : ?>
              <p class="alert alert-danger pl-3 py-1  alert-text fas fa-exclamation-triangle">
                <?= $_SESSION['errors']['verwijder-error']; ?>
              </p>
            <?php endif; ?>
          </small>
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Naam</th>
                    <th>Leeftijd</th>
                    <th>Postcode</th>
                    <th>Mobiel nummer</th>
                    <th>E-mail</th>
                    <th>Geboortedatum</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                while ($row = $result->fetch()) {
                ?>
                    <tr>
                        <td><?= $row['voornaam']; ?> </td>
                        <td><?= $row['achternaam']; ?> </td>
                        <td><?= $row['geboortedatum']; ?> </td>
                        <td><?= $row['telefoonnummer']; ?> </td>
                        <td><?= $row['email']; ?> </td>
                        <td><?= $row['toegangscode']; ?> </td>
                        <td><a href="lid_verwijder.php?id=<?= $row['id']; ?>" class="btn btn-danger">verwijder</a></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>