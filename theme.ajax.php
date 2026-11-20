<?php
include("csrf.inc.php");

session_start();

if (!isset($_POST["theme"])) {
    die();
}

// Save the theme in the session (server side)
$_SESSION["theme"] = $_POST["theme"];
