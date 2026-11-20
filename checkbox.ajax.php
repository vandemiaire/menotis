<?php
include("csrf.inc.php");

include("const.inc.php");
include("datas/config.php");
include("security.inc.php");
include("functions.inc.php");

// Hacking attempt
if (!checkCookie()) {
    die();
}

if (!isset($_POST["action"])) {
    http_response_code(400);
    exit("Invalid action");
}

if (!isset($_POST["uid"])) {
    http_response_code(400);
    exit("Invalid UID");
}

$uid = $_POST["uid"];

// Reject anything that is not a note UID (prevents path traversal)
if (!isValidNoteUid($uid)) {
    http_response_code(400);
    exit("Invalid UID");
}

// Content of the file
$datas = new stdClass();

// Existing note file
$filepath = "datas/" . $uid . ".json";
if (!is_file($filepath)) {
    http_response_code(404);
    exit("Note not found");
}
$json = file_get_contents($filepath);
$datas = json_decode($json);

// Get the content
$content = $datas->content;

switch ($_POST["action"]) {
    case "remove":

        // Remove all checkboxes
        $lignes = explode("\n", $content);

        $lignes = array_map(function ($ligne) {
            // Supprime le markdown si présent au début de la ligne
            return preg_replace('/^\s*\[( |x|X)\]\s*/', '', $ligne);
        }, $lignes);

        $content = implode("\n", $lignes);

        break;

    case "add":

        // Add all checkboxes
        $lignes = explode("\n", $content);

        $lignes = array_map(function ($ligne) {
            $ligne_trim = trim($ligne);

            // Ignore les lignes vides
            if ($ligne_trim === '') {
                return $ligne;
            }

            // Ignore les lignes qui ont déjà un [ ], [x] ou [X]
            if (preg_match('/^\s*\[( |x|X)\]/', $ligne)) {
                return $ligne;
            }

            // Ignore les lignes qui commencent par un titre Markdown (#, ##, ### ...)
            if (preg_match('/^\s*#{1,6}\s+/', $ligne)) {
                return $ligne;
            }

            // Ignore les images Markdown ![alt](url)
            if (preg_match('/^\s*!\[.*?\]\(.*?\)/', $ligne)) {
                return $ligne;
            }

            // Ignore les lignes de code / citations Markdown >
            if (preg_match('/^\s*>/', $ligne)) {
                return $ligne;
            }

            // Sinon, ajoute la case
            return "[ ] " . $ligne;
        }, $lignes);

        $content = implode("\n", $lignes);

        break;

    case "check":

        $index = isset($_POST["index"]) ? (int) $_POST["index"] : -1;
        $checked = !empty($_POST["checked"]);

        if ($index < 0) {
            http_response_code(400);
            exit("Invalid index");
        }

        // Replace checkboxes
        $i = 0;
        $content = preg_replace_callback(
            '/\[(x|X| )\]/',
            function ($m) use (&$i, $index, $checked) {
                if ($i++ === $index) {
                    return $checked ? '[x]' : '[ ]';
                }
                return $m[0];
            },
            $content
        );

        break;
}

// Put the content back in the object
$datas->content = $content;

// Last update is now
$datas->modified = time();

// Object -> JSON
$json = json_encode($datas);

// Save the JSON into the file
file_put_contents($filepath, $json);

// Return
echo json_encode(['status' => 'ok']);
