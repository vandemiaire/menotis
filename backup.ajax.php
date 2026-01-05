<?php

include("const.inc.php");
include("datas/config.php");
include("security.inc.php");

// Hacking attempt
if (!checkCookie()) {
    die();
}

// Init
$return = [];
$upload_dir = __DIR__ . '/datas/';
$maxSize = 10 * 1024 * 1024; // 10 Mo
$allowedMimeTypes = [
    'application/zip' => 'zip'
];

// Action must be set
if (!isset($_POST["action"])) {

    // No need to translate because it should never happen
    $return["error"] = "No action specified";
    exit(json_encode($return));
    die();
}

switch ($_POST["action"]) {
    case "restore":

        // Vérification fichier
        if (!isset($_FILES["uploadfile"]) || $_FILES["uploadfile"]["error"] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            exit(_("Error during upload"));
            die();
        }

        $file = $_FILES["uploadfile"];

        // Vérification taille
        if ($file["size"] > $maxSize) {
            http_response_code(400);
            exit(_("This file is too big"));
            die();
        }

        // Vérification MIME réel (pas l’extension)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (!array_key_exists($mimeType, $allowedMimeTypes)) {
            http_response_code(400);
            exit(_("This format is not allowed"));
            die();
        }

        // Destination temporaire
        $temp_zip_file = $upload_dir . "backup_" . bin2hex(random_bytes(16)) . '.zip';

        // Déplacement sécurisé
        if (!move_uploaded_file($file["tmp_name"], $temp_zip_file)) {
            http_response_code(500);
            exit(_("Unknown error while saving"));
        }

        $zip = new ZipArchive();

        if ($zip->open($temp_zip_file) === TRUE) {
            $zip->extractTo($upload_dir);
            $zip->close();
            echo "OK";
        } else {
            echo _("Unable to open the temporary ZIP file");
        }

        // Delete the temporary ZIP file
        unlink($temp_zip_file);

        break;

    case "backup":
        // ZIP object
        try {
            $zip = new ZipArchive();
        } catch (Exception $e) {
            $return["error"] = _("ZIP is not enabled on your PHP server");
            exit(json_encode($return));
            die();
        }

        // Clean old backup files
        foreach (glob("datas/*backup*.zip", GLOB_BRACE) as $file) {

            // Delete the file
            unlink($file);
        }

        // Destination
        $filename = strtolower(APP_NAME) . "_backup_" . date("Ymd_His", time()) . ".zip";
        $filepath = "datas/" . $filename;

        // Try to create the file
        if ($zip->open($filepath, ZipArchive::CREATE) !== TRUE) {
            $return["error"] = _("Unable to create the ZIP file");
            exit(json_encode($return));
            die();
        }

        // Loop on each type to backup
        foreach (glob("datas/*.{jpg,JPG,jpeg,JPEG,png,PNG,json}", GLOB_BRACE) as $file) {

            // Extract the file infos
            $pathinfo = pathinfo($file);

            // Add the file to the ZIP
            $zip->addFile($file, $pathinfo["basename"]);
        }

        // Values
        $return["error"] = "";
        $return["numfiles"] = $zip->numFiles;
        $return["status"] = $zip->status;
        $return["filename"] = $filename;
        $return["filepath"] = $filepath;

        // Close the ZIP
        $zip->close();

        // Return JSON
        echo json_encode($return);

        break;
}
