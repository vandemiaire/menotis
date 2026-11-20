<?php
include("csrf.inc.php");

session_start();

// Return the palettes
$colors = [];

// Get the theme, this SESSION var is sync with JS on "theme.js"
$theme = "dark";
if (isset($_SESSION["theme"])) {
    $theme = $_SESSION["theme"];
}

if ($theme == "dark") {
    $colors = ["0D1164", "640D5F", "EA2264", "37353E", "44444E", "B12C00", "1B4242", "116D6E", "1E5128", "C84B31"];
} else {

    // TODO !!!!!!!!!!!!!!!!!!!!
    $colors = ["F78D60", "E2703A"];
}

foreach ($colors as $color) {
    echo '<div onclick="return paletteSelect(this);" data-bgcolor="#' . $color . '" style="cursor:pointer;background-color:#' . $color . ';width:36px;height:36px;border-radius:18px;display:inline-block;">&nbsp;</div>' . PHP_EOL;
}
