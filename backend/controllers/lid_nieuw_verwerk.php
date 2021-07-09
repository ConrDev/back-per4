<?php
session_start();
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
  }
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


require_once '../config/config.inc.php';

/****************************************
   - Variables                         
****************************************/

echo 'adding variables...';

$voornaam           = $_POST['voornaam'];
$achternaam         = $_POST['achternaam'];
$geboortedatum      = $_POST['geboortedatum']; 
$telefoonnummer     = $_POST['telefoonnummer'];
$email              = $_POST['email'];

global $errors;
$errors             = [];

$pmail              = new PHPMailer(true);

/****************************************
   - Functions                         
****************************************/

echo 'adding functions...';

function mobiel_format($mobielnummer) {
    global $errors;
    $kentallen=array('0909','0906','0900','0842','0800','0676','06','010','046','0111','0475','0113','0478','0114','0481','0115','0485','0117','0486','0118','0487','013','0488','015','0492','0161','0493','0162','0495','0164','0497','0165','0499','0166','050','0167','0511','0168','0512','0172','0513','0174','0514','0180','0515','0181','0516','0182','0517','0183','0518','0184','0519','0186','0521','0187','0522','020','0523','0222','0524','0223','0525','0224','0527','0226','0528','0227','0529','0228','053','0229','0541','023','0543','024','0544','0251','0545','0252','0546','0255','0547','026','0548','0294','055','0297','0561','0299','0562','030','0566','0313','0570','0314','0571','0315','0572','0316','0573','0317','0575','0318','0577','0320','0578','0321','058','033','0591','0341','0592','0342','0593','0343','0594','0344','0595','0345','0596','0346','0597','0347','0598','0348','0599','035','070','036','071','038','072','040','073','0411','074','0412','075','0413','076','0416','077','0418','078','043','079','045');
    $mobielnummer=preg_replace('/[^0-9]/', '', $mobielnummer);

    if(substr($mobielnummer,0,2)=='31') {
        $mobielnummer=preg_replace('/^([0-9]{2})([0-9]+)/',  '$2', $mobielnummer);
    }
    
    if(substr($mobielnummer,0,1)=='+' && substr($mobielnummer,1,2)=='31'){
        for($i=4; $i>=0; $i--) {
            $ken=substr($mobielnummer,4,$i);
            if(in_array($ken,$kentallen))
                break;
        }
    } else {
        for($i=4; $i>=0; $i--) {
            $ken=substr($mobielnummer,0,$i);
            if(in_array($ken,$kentallen))
                break;
        }
    }
    
    if (strlen($mobielnummer) < 9 || strlen($mobielnummer) == 0) {
        $errors['mobiel'] = "het mobiel nummer is fout, 06...";
        return "";
    } else {
        return htmlspecialchars(preg_replace('/([0-9]{'.$i.'})([0-9]+)/', "+31 $1 $2", $mobielnummer));
    }
}

function email_format($email) {
    global $errors;
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $pattern = '/[1-9][0-9]{4}@glr\.nl/';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "is niet geldig";
        return "";
    } else {
        if (!preg_match($pattern, $email)) {
            $errors['email'] = "is geen GLR email!";
            return "";
        } else {
            return htmlspecialchars($email);
        }
    }
}

function naam_format($naam) {
    global $errors;
    $naam = htmlentities($naam, ENT_QUOTES);
    $pattern = "/^[a-zA-Z ]*$/";
    // $errors['naam'] = "is ttttniet goed!";
    if (strlen($naam) == 0) {
        $errors['naam'] = "is niet goed!";
        return "test";
    } else {
        if (!preg_match($pattern, $naam)) {
            $errors['naam'] = "heeft niet het juiste patroon!";
            return "test2"; 
        } else {
            return htmlspecialchars($naam);
        }
    }
}

function geboortedatum_format($geboortedatum) {
    global $errors;
    // $geboortedatum = str_replace("-", "", $geboortedatum);
    $pattern = "/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
        
    if (!preg_match($pattern, $geboortedatum)) {
        $errors['datum'] = "heeft niet het juiste patroon!";
        return ""; 
    } else {
        $geboortedatum = preg_replace($pattern, "$3-$2-$1", $geboortedatum);
        return htmlspecialchars($geboortedatum);
    }
}

function postCheck($post) {
    global $errors;
    $post = $_POST[$post];
    
    if (strlen($post) > 0) {
        return htmlspecialchars($post);
    } else {
        $errors['postCheck'] = "is niet ingevuld!";
        return "";
    }
}

function uuid() {
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function toegangscode() {
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s', str_split(bin2hex($data), 4));
}
                      
/***************************************/

echo 'checking post...';
// if (isset($_POST['add'])) {
    echo 'checking variables...';
    if (!isset($voornaam) || !isset($achternaam) ||
        !isset($geboortedatum) || !isset($telefoonnummer) ||
        !isset($email))
    {
        $errors['aanpassen_mislukt'] = "Niet alle velden zijn meegestuurd!";
        // header('Location: .../../index.php');
        // return;
    }

    $sql = "SELECT * FROM `leden` WHERE `email`= '$email'";
    $result = $mysqli->query($sql);
    if ($result->rowCount() == 1) {
        $errors['email'] = "email is al ingeschreven!";
        // return;
    }

    $query = "INSERT INTO leden (id, voornaam, achternaam, geboortedatum, telefoonnummer, email, toegangscode) 
                    VALUES (:id, :voornaam, :achternaam, :geboortedatum, :telefoonnummer, :email, :toegangscode)";

    if ($stmt = $mysqli->prepare($query)) {

        $values = [
            ":id" => "" . uuid(),
            ":voornaam" => naam_format($voornaam),
            ":achternaam" => naam_format($achternaam),
            ":geboortedatum" => $geboortedatum,
            ":telefoonnummer" => mobiel_format($telefoonnummer),
            ":email" => email_format($email),
            ":toegangscode" => toegangscode(),
        ];
        
        if (count($errors) > 0) {
            echo 'failed: ' . count($errors);
            $_SESSION['errors'] = $errors;
            var_dump($_SESSION['errors']);
            header('Location: .../../../../index.php');
            return;
        } else {
            echo 'accepted sednging mail';
            if ($stmt->execute($values)) {
                //Server settings
                $pmail->isSMTP();                                              // Set mailer to use SMTP
                // $pmail->Host = 'smtp.gmail.com';                             // Specify main and backup SMTP servers
                // $pmail->SMTPAuth = true;                                       // Enable SMTP authentication
                // $pmail->Username = 'djcreeper1001@gmail.com';                           // SMTP username
                // $pmail->Password = 'conner12cvt';                           // SMTP password
                $pmail->Host = 'smtp.mailtrap.io';                             // Specify main and backup SMTP servers
                $pmail->SMTPAuth = true;                                       // Enable SMTP authentication
                $pmail->Username = 'c11479dbeef6ca';                           // SMTP username
                $pmail->Password = '6a857885fbaba8';                           // SMTP password
                $pmail->SMTPSecure = 'tls';
                $pmail->Port = 587;

                $pmail->setFrom('noreply@example.com', 'GLR');
                $pmail->addAddress($email, $voornaam . " " . $achternaam);

                $pmail->isHTML(true);
                $pmail->CharSet = 'UTF-8';
                $pmail->Encoding = 'base64';
                $pmail->Subject = 'GLR Inschrijving Wedstrijd';
                $pmail->AltBody = "HTML Emails not supported by your Client.";
                $pmail->Body    = '
                    <!doctype html>
                    <html ⚡4email data-css-strict>
                    <head>
                        <meta charset="utf-8" />
                        <style amp4email-boilerplate>
                        body {
                            visibility: hidden;
                        }
                        </style>
                        <script async src="https://cdn.ampproject.org/v0.js"></script>
                        <style amp-custom>
                        .es-desk-hidden {
                            display: none;
                            float: left;
                            overflow: hidden;
                            width: 0;
                            max-height: 0;
                            line-height: 0;
                        }
                        .es-button-border:hover {
                            border-style: solid solid solid solid;
                            background: #0b317e;
                            border-color: #42d159 #42d159 #42d159 #42d159;
                        }
                        .es-button-border:hover a.es-button,
                        .es-button-border:hover button.es-button {
                            background: #0b317e;
                            border-color: #0b317e;
                        }
                        body {
                            width: 100%;
                            font-family: roboto, "helvetica neue", helvetica, arial, sans-serif;
                        }
                        table {
                            border-collapse: collapse;
                            border-spacing: 0px;
                        }
                        table td,
                        body,
                        .es-wrapper {
                            padding: 0;
                            margin: 0;
                        }
                        .es-content,
                        .es-header,
                        .es-footer {
                            table-layout: fixed;
                            width: 100%;
                        }
                        p,
                        hr {
                            margin: 0;
                        }
                        h1,
                        h2,
                        h3,
                        h4,
                        h5 {
                            margin: 0;
                            line-height: 120%;
                            font-family: roboto, "helvetica neue", helvetica, arial, sans-serif;
                        }
                        .es-left {
                            float: left;
                        }
                        .es-right {
                            float: right;
                        }
                        .es-p5 {
                            padding: 5px;
                        }
                        .es-p5t {
                            padding-top: 5px;
                        }
                        .es-p5b {
                            padding-bottom: 5px;
                        }
                        .es-p5l {
                            padding-left: 5px;
                        }
                        .es-p5r {
                            padding-right: 5px;
                        }
                        .es-p10 {
                            padding: 10px;
                        }
                        .es-p10t {
                            padding-top: 10px;
                        }
                        .es-p10b {
                            padding-bottom: 10px;
                        }
                        .es-p10l {
                            padding-left: 10px;
                        }
                        .es-p10r {
                            padding-right: 10px;
                        }
                        .es-p15 {
                            padding: 15px;
                        }
                        .es-p15t {
                            padding-top: 15px;
                        }
                        .es-p15b {
                            padding-bottom: 15px;
                        }
                        .es-p15l {
                            padding-left: 15px;
                        }
                        .es-p15r {
                            padding-right: 15px;
                        }
                        .es-p20 {
                            padding: 20px;
                        }
                        .es-p20t {
                            padding-top: 20px;
                        }
                        .es-p20b {
                            padding-bottom: 20px;
                        }
                        .es-p20l {
                            padding-left: 20px;
                        }
                        .es-p20r {
                            padding-right: 20px;
                        }
                        .es-p25 {
                            padding: 25px;
                        }
                        .es-p25t {
                            padding-top: 25px;
                        }
                        .es-p25b {
                            padding-bottom: 25px;
                        }
                        .es-p25l {
                            padding-left: 25px;
                        }
                        .es-p25r {
                            padding-right: 25px;
                        }
                        .es-p30 {
                            padding: 30px;
                        }
                        .es-p30t {
                            padding-top: 30px;
                        }
                        .es-p30b {
                            padding-bottom: 30px;
                        }
                        .es-p30l {
                            padding-left: 30px;
                        }
                        .es-p30r {
                            padding-right: 30px;
                        }
                        .es-p35 {
                            padding: 35px;
                        }
                        .es-p35t {
                            padding-top: 35px;
                        }
                        .es-p35b {
                            padding-bottom: 35px;
                        }
                        .es-p35l {
                            padding-left: 35px;
                        }
                        .es-p35r {
                            padding-right: 35px;
                        }
                        .es-p40 {
                            padding: 40px;
                        }
                        .es-p40t {
                            padding-top: 40px;
                        }
                        .es-p40b {
                            padding-bottom: 40px;
                        }
                        .es-p40l {
                            padding-left: 40px;
                        }
                        .es-p40r {
                            padding-right: 40px;
                        }
                        .es-menu td {
                            border: 0;
                        }
                        s {
                            text-decoration: line-through;
                        }
                        p,
                        ul li,
                        ol li {
                            font-family: roboto, "helvetica neue", helvetica, arial, sans-serif;
                            line-height: 150%;
                        }
                        ul li,
                        ol li {
                            margin-bottom: 15px;
                        }
                        a {
                            text-decoration: underline;
                        }
                        .es-menu td a {
                            text-decoration: none;
                            display: block;
                        }
                        .es-menu amp-img,
                        .es-button amp-img {
                            vertical-align: middle;
                        }
                        .es-wrapper {
                            width: 100%;
                            height: 100%;
                        }
                        .es-wrapper-color {
                            background-color: #f8f9fd;
                        }
                        .es-header {
                            background-color: transparent;
                        }
                        .es-header-body {
                            background-color: transparent;
                        }
                        .es-header-body p,
                        .es-header-body ul li,
                        .es-header-body ol li {
                            color: #333333;
                            font-size: 14px;
                        }
                        .es-header-body a {
                            color: #1376c8;
                            font-size: 14px;
                        }
                        .es-content-body {
                            background-color: transparent;
                        }
                        .es-content-body p,
                        .es-content-body ul li,
                        .es-content-body ol li {
                            color: #131313;
                            font-size: 16px;
                        }
                        .es-content-body a {
                            color: #2cb543;
                            font-size: 16px;
                        }
                        .es-footer {
                            background-color: #0a2b6e;
                            background-image: url(https://hpy.stripocdn.email/content/guids/CABINET_9bfedeeeb9eeabe76f8ff794c5e228f9/images/2191625641866113.png);
                            background-repeat: repeat;
                            background-position: center center;
                        }
                        .es-footer-body {
                            background-color: transparent;
                        }
                        .es-footer-body p,
                        .es-footer-body ul li,
                        .es-footer-body ol li {
                            color: #212121;
                            font-size: 16px;
                        }
                        .es-footer-body a {
                            color: #ffffff;
                            font-size: 16px;
                        }
                        .es-infoblock,
                        .es-infoblock p,
                        .es-infoblock ul li,
                        .es-infoblock ol li {
                            line-height: 120%;
                            font-size: 12px;
                            color: #ffffff;
                        }
                        .es-infoblock a {
                            font-size: 12px;
                            color: #ffffff;
                        }
                        h1 {
                            font-size: 30px;
                            font-style: normal;
                            font-weight: bold;
                            color: #212121;
                        }
                        h2 {
                            font-size: 24px;
                            font-style: normal;
                            font-weight: bold;
                            color: #212121;
                        }
                        h3 {
                            font-size: 20px;
                            font-style: normal;
                            font-weight: normal;
                            color: #212121;
                        }
                        .es-header-body h1 a,
                        .es-content-body h1 a,
                        .es-footer-body h1 a {
                            font-size: 30px;
                        }
                        .es-header-body h2 a,
                        .es-content-body h2 a,
                        .es-footer-body h2 a {
                            font-size: 24px;
                        }
                        .es-header-body h3 a,
                        .es-content-body h3 a,
                        .es-footer-body h3 a {
                            font-size: 20px;
                        }
                        a.es-button,
                        button.es-button {
                            border-style: solid;
                            border-color: #071f4f;
                            border-width: 10px 20px 10px 20px;
                            display: inline-block;
                            background: #071f4f;
                            border-radius: 5px;
                            font-size: 16px;
                            font-family: roboto, "helvetica neue", helvetica, arial, sans-serif;
                            font-weight: normal;
                            font-style: normal;
                            line-height: 120%;
                            color: #ffffff;
                            text-decoration: none;
                            width: auto;
                            text-align: center;
                        }
                        .es-button-border {
                            border-style: solid solid solid solid;
                            border-color: #2cb543 #2cb543 #2cb543 #2cb543;
                            background: #071f4f;
                            border-width: 0px 0px 0px 0px;
                            display: inline-block;
                            border-radius: 5px;
                            width: auto;
                        }
                        .es-button img {
                            display: inline-block;
                            vertical-align: middle;
                        }
                        .es-p-default {
                            padding-top: 0px;
                            padding-right: 0px;
                            padding-bottom: 0px;
                            padding-left: 0px;
                        }
                        .es-p-all-default {
                            padding: 0px;
                        }
                        @media only screen and (max-width: 600px) {
                            .st-br {
                            padding-left: 10px;
                            padding-right: 10px;
                            }
                            p,
                            ul li,
                            ol li,
                            a {
                            line-height: 150%;
                            }
                            h1 {
                            font-size: 30px;
                            text-align: center;
                            line-height: 120%;
                            }
                            h2 {
                            font-size: 26px;
                            text-align: center;
                            line-height: 120%;
                            }
                            h3 {
                            font-size: 20px;
                            text-align: center;
                            line-height: 120%;
                            }
                            h1 a {
                            text-align: center;
                            }
                            .es-header-body h1 a,
                            .es-content-body h1 a,
                            .es-footer-body h1 a {
                            font-size: 30px;
                            }
                            h2 a {
                            text-align: center;
                            }
                            .es-header-body h2 a,
                            .es-content-body h2 a,
                            .es-footer-body h2 a {
                            font-size: 26px;
                            }
                            h3 a {
                            text-align: center;
                            }
                            .es-header-body h3 a,
                            .es-content-body h3 a,
                            .es-footer-body h3 a {
                            font-size: 20px;
                            }
                            .es-menu td a {
                            font-size: 14px;
                            }
                            .es-header-body p,
                            .es-header-body ul li,
                            .es-header-body ol li,
                            .es-header-body a {
                            font-size: 16px;
                            }
                            .es-content-body p,
                            .es-content-body ul li,
                            .es-content-body ol li,
                            .es-content-body a {
                            font-size: 16px;
                            }
                            .es-footer-body p,
                            .es-footer-body ul li,
                            .es-footer-body ol li,
                            .es-footer-body a {
                            font-size: 14px;
                            }
                            .es-infoblock p,
                            .es-infoblock ul li,
                            .es-infoblock ol li,
                            .es-infoblock a {
                            font-size: 12px;
                            }
                            *[class="gmail-fix"] {
                            display: none;
                            }
                            .es-m-txt-c,
                            .es-m-txt-c h1,
                            .es-m-txt-c h2,
                            .es-m-txt-c h3 {
                            text-align: center;
                            }
                            .es-m-txt-r,
                            .es-m-txt-r h1,
                            .es-m-txt-r h2,
                            .es-m-txt-r h3 {
                            text-align: right;
                            }
                            .es-m-txt-l,
                            .es-m-txt-l h1,
                            .es-m-txt-l h2,
                            .es-m-txt-l h3 {
                            text-align: left;
                            }
                            .es-m-txt-r amp-img {
                            float: right;
                            }
                            .es-m-txt-c amp-img {
                            margin: 0 auto;
                            }
                            .es-m-txt-l amp-img {
                            float: left;
                            }
                            .es-button-border {
                            display: block;
                            }
                            a.es-button,
                            button.es-button {
                            font-size: 16px;
                            display: block;
                            border-left-width: 0px;
                            border-right-width: 0px;
                            }
                            .es-adaptive table,
                            .es-left,
                            .es-right {
                            width: 100%;
                            }
                            .es-content table,
                            .es-header table,
                            .es-footer table,
                            .es-content,
                            .es-footer,
                            .es-header {
                            width: 100%;
                            max-width: 600px;
                            }
                            .es-adapt-td {
                            display: block;
                            width: 100%;
                            }
                            .adapt-img {
                            width: 100%;
                            height: auto;
                            }
                            td.es-m-p0 {
                            padding: 0;
                            }
                            td.es-m-p0r {
                            padding-right: 0;
                            }
                            td.es-m-p0l {
                            padding-left: 0;
                            }
                            td.es-m-p0t {
                            padding-top: 0;
                            }
                            td.es-m-p0b {
                            padding-bottom: 0;
                            }
                            td.es-m-p20b {
                            padding-bottom: 20px;
                            }
                            .es-mobile-hidden,
                            .es-hidden {
                            display: none;
                            }
                            tr.es-desk-hidden,
                            td.es-desk-hidden,
                            table.es-desk-hidden {
                            width: auto;
                            overflow: visible;
                            float: none;
                            max-height: inherit;
                            line-height: inherit;
                            }
                            tr.es-desk-hidden {
                            display: table-row;
                            }
                            table.es-desk-hidden {
                            display: table;
                            }
                            td.es-desk-menu-hidden {
                            display: table-cell;
                            }
                            table.es-table-not-adapt,
                            .esd-block-html table {
                            width: auto;
                            }
                            table.es-social {
                            display: inline-block;
                            }
                            table.es-social td {
                            display: inline-block;
                            }
                            td.es-m-p5 {
                            padding: 5px;
                            }
                            td.es-m-p5t {
                            padding-top: 5px;
                            }
                            td.es-m-p5b {
                            padding-bottom: 5px;
                            }
                            td.es-m-p5r {
                            padding-right: 5px;
                            }
                            td.es-m-p5l {
                            padding-left: 5px;
                            }
                            td.es-m-p10 {
                            padding: 10px;
                            }
                            td.es-m-p10t {
                            padding-top: 10px;
                            }
                            td.es-m-p10b {
                            padding-bottom: 10px;
                            }
                            td.es-m-p10r {
                            padding-right: 10px;
                            }
                            td.es-m-p10l {
                            padding-left: 10px;
                            }
                            td.es-m-p15 {
                            padding: 15px;
                            }
                            td.es-m-p15t {
                            padding-top: 15px;
                            }
                            td.es-m-p15b {
                            padding-bottom: 15px;
                            }
                            td.es-m-p15r {
                            padding-right: 15px;
                            }
                            td.es-m-p15l {
                            padding-left: 15px;
                            }
                            td.es-m-p20 {
                            padding: 20px;
                            }
                            td.es-m-p20t {
                            padding-top: 20px;
                            }
                            td.es-m-p20r {
                            padding-right: 20px;
                            }
                            td.es-m-p20l {
                            padding-left: 20px;
                            }
                            td.es-m-p25 {
                            padding: 25px;
                            }
                            td.es-m-p25t {
                            padding-top: 25px;
                            }
                            td.es-m-p25b {
                            padding-bottom: 25px;
                            }
                            td.es-m-p25r {
                            padding-right: 25px;
                            }
                            td.es-m-p25l {
                            padding-left: 25px;
                            }
                            td.es-m-p30 {
                            padding: 30px;
                            }
                            td.es-m-p30t {
                            padding-top: 30px;
                            }
                            td.es-m-p30b {
                            padding-bottom: 30px;
                            }
                            td.es-m-p30r {
                            padding-right: 30px;
                            }
                            td.es-m-p30l {
                            padding-left: 30px;
                            }
                            td.es-m-p35 {
                            padding: 35px;
                            }
                            td.es-m-p35t {
                            padding-top: 35px;
                            }
                            td.es-m-p35b {
                            padding-bottom: 35px;
                            }
                            td.es-m-p35r {
                            padding-right: 35px;
                            }
                            td.es-m-p35l {
                            padding-left: 35px;
                            }
                            td.es-m-p40 {
                            padding: 40px;
                            }
                            td.es-m-p40t {
                            padding-top: 40px;
                            }
                            td.es-m-p40b {
                            padding-bottom: 40px;
                            }
                            td.es-m-p40r {
                            padding-right: 40px;
                            }
                            td.es-m-p40l {
                            padding-left: 40px;
                            }
                        }
                        </style>
                    </head>
                    <body>
                        <div class="es-wrapper-color">
                        <!--[if gte mso 9
                            ]><v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                            <v:fill type="tile" color="#f8f9fd"></v:fill> </v:background
                        ><![endif]-->
                        <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                            <td valign="top">
                                <table
                                class="es-header"
                                cellspacing="0"
                                cellpadding="0"
                                align="center"
                                >
                                <tr>
                                    <td align="center">
                                    <table
                                        class="es-header-body"
                                        width="600"
                                        cellspacing="0"
                                        cellpadding="0"
                                        bgcolor="#ffffff"
                                        align="center"
                                    >
                                        <tr>
                                        <td class="es-p10t es-p15b es-p30r es-p30l" align="left">
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="540" valign="top" align="center">
                                                <table
                                                    width="100%"
                                                    cellspacing="0"
                                                    cellpadding="0"
                                                    role="presentation"
                                                >
                                                    <tr>
                                                    <td style="font-size: 0px" align="center">
                                                        <amp-img
                                                        src="https://pjeeaf.stripocdn.email/content/guids/586f4f36-83b1-4a81-b614-20a45fb14581/images/88901625753922631.png"
                                                        alt
                                                        style="display: block"
                                                        width="130"
                                                        height="130"
                                                        ></amp-img>
                                                    </td>
                                                    </tr>
                                                </table>
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                                </table>
                                <table
                                class="es-content"
                                cellspacing="0"
                                cellpadding="0"
                                align="center"
                                >
                                <tr>
                                    <td
                                    style="
                                        background-color: #f8f9fd;
                                        background-image: url(https://hpy.stripocdn.email/content/guids/CABINET_1ce849b9d6fc2f13978e163ad3c663df/images/10801592857268437.png);
                                        background-repeat: no-repeat;
                                        background-position: center top;
                                    "
                                    bgcolor="#f8f9fd"
                                    align="center"
                                    >
                                    <table
                                        class="es-content-body"
                                        style="background-color: transparent"
                                        width="600"
                                        cellspacing="0"
                                        cellpadding="0"
                                        bgcolor="transparent"
                                        align="center"
                                    >
                                        <tr>
                                        <td class="es-p20t es-p10b es-p20r es-p20l" align="left">
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="560" valign="top" align="center">
                                                <table
                                                    width="100%"
                                                    cellspacing="0"
                                                    cellpadding="0"
                                                    role="presentation"
                                                >
                                                    <tr>
                                                    <td class="es-p10b" align="center">
                                                        <h1>Inschrijving Succesvol!</h1>
                                                    </td>
                                                    </tr>
                                                    <tr>
                                                    <td class="es-p20" align="center">
                                                        Beste '. $voornaam. ' ' . $achternaam . ' bij deze bent u
                                                        ingeschreven voor de wedstrijd
                                                    </td>
                                                    </tr>
                                                </table>
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                                </table>
                                <table
                                class="es-footer"
                                style="background-position: center center"
                                cellspacing="0"
                                cellpadding="0"
                                align="center"
                                >
                                <tr>
                                    <td align="center">
                                    <table
                                        class="es-footer-body"
                                        width="600"
                                        cellspacing="0"
                                        cellpadding="0"
                                        bgcolor="rgba(0, 0, 0, 0)"
                                        align="center"
                                    >
                                        <tr>
                                        <td
                                            class="
                                            es-p40t
                                            es-p40b
                                            es-m-p40t
                                            es-m-p40b
                                            es-m-p20r
                                            es-m-p20l
                                            "
                                            align="left"
                                        >
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="600" valign="top" align="center">
                                                <table
                                                    style="
                                                    background-color: #f0f3fe;
                                                    border-radius: 20px;
                                                    border-collapse: separate;
                                                    "
                                                    width="100%"
                                                    cellspacing="0"
                                                    cellpadding="0"
                                                    bgcolor="#f0f3fe"
                                                    role="presentation"
                                                >
                                                    <tr>
                                                    <td
                                                        class="es-p25t es-p10b es-p20r es-p20l"
                                                        align="left"
                                                    >
                                                        <h1
                                                        style="
                                                            text-align: center;
                                                            line-height: 150%;
                                                        "
                                                        >
                                                        Toegangscode:<br />
                                                        </h1>
                                                    </td>
                                                    </tr>
                                                    <tr>
                                                    <td class="es-p20" align="center">
                                                        <p style="line-height: 200%">
                                                        ' . toegangscode() . '
                                                        </p>
                                                    </td>
                                                    </tr>
                                                </table>
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                                </table>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </body>
                    </html>
                ';

                $pmail->send();
                header('Location: ../../index.php');
                return;
            } else {
                $errors['toevoeg_mislukt'] = "Fout bij het toevoegen: " .$stmt->errorInfo()[2]; 
                header('Location: ../../index.php');
                return;
            }
        }
    } else {
        header('Location: ../../index.php');
        return;
    }
// }

// if (strlen($voornaam) > 0 &&
//     strlen($achternaam) > 0 &&
//     strlen($geboortedatum) > 0 &&
//     strlen($telefoonnummer) > 0 &&
//     strlen($email) > 0) {
    
//     $check1 = strtotime($birth_date);
//     if (date('Y-m-d', $check1) == $birth_date) {


//         $query = "INSERT INTO `back2_leden` (`voornaam`, `achternaam`, `geboortedatum`, `telefoonnummer`, `email`, `toegangscode`) 
//                     VALUES ('$voornaam', '$achternaam', '$geboortedatum', '" . mobiel_format($_POST['mobielnummerVeld']) . "', '$email', '" . toegangscode() . "')";

//         $result = mysqli_query($link, $query);

//         if ($result) {
//             header('Location: ../../pages/home.php');
//             exit;
//         } else {
//             echo 'Er ging wat mis met het toevoegen!';
//         }
//     } else {
//         echo 'Een van de ingevulde data was ongeldig!';
//     }
// } else {
//     echo 'Niet alle velden waren ingevuld!';
// }
