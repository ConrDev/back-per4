<?php
session_start();

include '../backend/config/config.inc.php';

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

    <div class="modal-content col-5 mx-auto p-5">
        <a class="btn btn-info text-center mb-5" style="margin-left: 30rem !important;" href="../index.php">Terug</a>
        <!-- <form class="form" method="post" action="./index.php"> -->

        <h2 for="" class="text-center mb-2 ">Inloggen</h2>
        <form method="post" action="../login.php">
            <div class="form-group">
                <label for="my-textarea">Gebruikersnaam</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="">
            </div>
            <div class="form-group">
                <label for="my-textarea">Wachtwoord</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="">
                <small name="emailError" id="emailError" class="form-text text-muted">
                    <?php if (!empty($_SESSION['errors']['login-error'])) : ?>
                        <p class="alert alert-danger pl-3 py-1  alert-text fas fa-exclamation-triangle">
                            <?= $_SESSION['errors']['login-error']; ?>
                        </p>
                    <?php endif; ?>
                </small>
            </div>

            <button type="submit" naam="login" id="login" class="btn btn-info text-center">Inschrijven</button>
        </form>


        <p for="" class="text-center mt-5 text-secondary">&copy; Grafisch Lyceum Rotterdam 2021</p>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>