<?php

include("const.inc.php");
include("datas/config.php");
include("security.inc.php");

// Ensure action is provided
if (!isset($_POST["action"])) {
    http_response_code(400);
    exit("Missing action");
}

// Hacking attempt
if (!checkCookie()) {
    http_response_code(400);
    exit("No auth");
    die();
}

switch ($_POST["action"]) {
    case "delete":

        // Ensure basename is provided
        if (empty($_POST["basename"])) {
            http_response_code(400);
            exit("Missing filename");
        }

        // Only keep the filename (prevents directory traversal)
        $basename = basename($_POST["basename"]);

        // Allow only specific extensions
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $basename)) {
            http_response_code(400);
            exit("Invalid file type");
        }

        // Build the absolute file path
        $file = __DIR__ . "/datas/" . $basename;

        // Check file existence
        if (!file_exists($file)) {
            http_response_code(404);
            exit("File not found");
        }

        // Attempt deletion
        if (!unlink($file)) {
            http_response_code(500);
            exit("Unable to delete file");
        }

        // Success response
        echo "OK";

        break;
}
