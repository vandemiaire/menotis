<?php

session_start();

// This is SVG
header('Content-Type: image/svg+xml');

// Light when dark, and vice versa
switch ($_SESSION["theme"]) {
    case "dark":
        $color = "#ffffff";
        break;
    case "light":
        $color = "#000000";
        break;
    default:
        $color = "#000000";
}

// Dynamic SVG
echo <<<XML
<?xml version="1.0" encoding="utf-8"?>
<svg fill="{$color}" width="800px" height="800px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
<title>ellipsis-vertical-filled</title>
<path d="M20.25 16c0 2.347-1.903 4.25-4.25 4.25s-4.25-1.903-4.25-4.25c0-2.347 1.903-4.25 4.25-4.25s4.25 1.903 4.25 4.25zM20.25 27c0 2.347-1.903 4.25-4.25 4.25s-4.25-1.903-4.25-4.25c0-2.347 1.903-4.25 4.25-4.25s4.25 1.903 4.25 4.25zM20.25 5c0 2.347-1.903 4.25-4.25 4.25s-4.25-1.903-4.25-4.25c0-2.347 1.903-4.25 4.25-4.25s4.25 1.903 4.25 4.25z"></path>
</svg>
XML;
