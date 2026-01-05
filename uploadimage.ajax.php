<?php

include("datas/config.php");
include("security.inc.php");

// Hacking attempt
if (!checkCookie()) {
    die();
}

// Configuration
$uid = "";
$title = "";
$upload_dir = __DIR__ . '/datas/';
$maxSize = 10 * 1024 * 1024; // 10 Mo
$allowedMimeTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png'
];

// UID
if (isset($_POST["uid"])) {
    $uid = $_POST["uid"];
} else {
    http_response_code(400);
    exit(_("No UID"));
}

// Title
if (isset($_POST["title"])) {
    $title = $_POST["title"];
}

// Vérification fichier
if (!isset($_FILES["uploadfile"]) || $_FILES["uploadfile"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit(_("Error during upload"));
}

$file = $_FILES["uploadfile"];

// Vérification taille
if ($file["size"] > $maxSize) {
    http_response_code(400);
    exit(_("This file is too big"));
}

// Vérification MIME réel (pas l’extension)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);

if (!array_key_exists($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    exit(_("This format is not allowed"));
}

// Extension forcée selon MIME (anti spoofing)
$extension = $allowedMimeTypes[$mimeType];

// Nom de fichier sécurisé (aucun nom client)
$filename = bin2hex(random_bytes(16)) . '.' . $extension;
$destination = $upload_dir . $filename;

// Déplacement sécurisé
if (!move_uploaded_file($file["tmp_name"], $destination)) {
    http_response_code(500);
    exit(_("Unknown error while saving"));
}

// Existing note file
$filepath = "datas/" . $uid . ".json";
$json = file_get_contents($filepath);
$datas = json_decode($json);

// Add the markdown link
$datas->content .= PHP_EOL . "![" . $title . "](datas/" . $filename . ")";
$datas->modified=time();

// Save the file
$json = json_encode($datas);
file_put_contents($filepath, $json);

echo "OK";
