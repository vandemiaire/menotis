<?php
include("csrf.inc.php");

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

        // The ZIP is read directly from PHP's temporary upload file: it is never copied into the
        // public "datas/" folder, and it is NEVER extracted as is (extractTo() would accept any file,
        // e.g. a ".php" script that becomes executable, or a "config.php" that replaces ours).
        $zip = new ZipArchive();
        if ($zip->open($file["tmp_name"]) !== TRUE) {
            http_response_code(400);
            exit(_("Unable to open the temporary ZIP file"));
        }

        // Only what "backup" produces is accepted: notes (JSON) and images (PNG/JPEG), flat, no folder
        $maxEntrySize = 20 * 1024 * 1024;   // 20 Mo per file
        $maxTotalSize = 200 * 1024 * 1024;  // 200 Mo uncompressed (protection against "ZIP bombs")
        $imageMimeTypes = ["image/png", "image/jpeg"];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $toRestore = [];
        $totalSize = 0;

        // Pass 1: check EVERYTHING before writing anything (a rejected ZIP changes nothing)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat["name"];

            // Folders (e.g. created by some ZIP tools) are ignored
            if (substr($name, -1) === "/") {
                continue;
            }

            $isNote = preg_match('/^note[a-zA-Z0-9]{1,64}\.json$/', $name) === 1;
            $isImage = preg_match('/^[a-zA-Z0-9_-]{1,64}\.(png|jpe?g)$/i', $name) === 1;

            // Anything else (".php", "config.php", sub-folders, "../", ...) rejects the whole ZIP
            if (!$isNote && !$isImage) {
                $zip->close();
                http_response_code(400);
                exit(sprintf(_("This backup contains a file that is not allowed: %s"), htmlspecialchars($name, ENT_QUOTES, 'UTF-8')));
            }

            // Size checks (declared size, then real size once read)
            $totalSize += $stat["size"];
            if ($stat["size"] > $maxEntrySize || $totalSize > $maxTotalSize) {
                $zip->close();
                http_response_code(400);
                exit(_("This file is too big"));
            }
            $content = $zip->getFromIndex($i, $maxEntrySize + 1);
            if ($content === false || strlen($content) > $maxEntrySize) {
                $zip->close();
                http_response_code(400);
                exit(_("This file is too big"));
            }

            // The content must match the type: a real note or a real image
            $valid = $isNote
                ? is_object(json_decode($content))
                : in_array($finfo->buffer($content), $imageMimeTypes, true);
            if (!$valid) {
                $zip->close();
                http_response_code(400);
                exit(sprintf(_("This backup contains a file that is not allowed: %s"), htmlspecialchars($name, ENT_QUOTES, 'UTF-8')));
            }

            // Only the index is kept (not the content) to limit memory usage
            $toRestore[$i] = $name;
            unset($content);
        }

        // Pass 2: write the checked files (names are validated, so they stay inside "datas/")
        foreach ($toRestore as $i => $name) {
            $content = $zip->getFromIndex($i, $maxEntrySize + 1);
            if ($content === false || file_put_contents($upload_dir . $name, $content) === false) {
                $zip->close();
                http_response_code(500);
                exit(_("Unknown error while saving"));
            }
        }
        $zip->close();

        echo "OK";

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
