<?php

session_start();

include("const.inc.php");
include("datas/config.php");
include("security.inc.php");
include("functions.inc.php");

// Hacking attempt
if (!checkCookie()) {
    http_response_code(400);
    exit("No auth");
}

// Basic security check
if (!isset($_POST["action"])) die("No action");
if (!isset($_POST["uid"])) die("No file ID");

// Init
$sortingmax = 0;
$uid = "";
$datas = new stdClass();

// File content
if ($_POST["uid"] <> "") {

    // Existing UID
    $uid = $_POST["uid"];

    // Existing note file
    $filepath = "datas/" . $_POST["uid"] . ".json";
    $json = file_get_contents($filepath);
    $datas = json_decode($json);
} else {

    // New UID
    $uid = guid();

    // New note file
    $filepath = "datas/note" . $uid . ".json";

    // Max sorting
    if (isset($_SESSION["sortingmax"])) {

        // Get the max sorting
        $sortingmax = intval($_SESSION["sortingmax"]);

        // Next free sorting
        $sortingmax++;

        // Assign the new sorting to the file
        $datas->sorting = $sortingmax;

        // Save the new sorting max
        $_SESSION["sortingmax"] = $sortingmax;
    } else {

        // Should only happen if this is the first note
        $sortingmax = 1;
        $datas->sorting = $sortingmax;
        $_SESSION["sortingmax"] = $sortingmax;
    }
}

switch ($_POST["action"]) {
    case "pin":

        // Already pinned once ?
        if (isset($datas->pinned)) {

            // Invert
            $datas->pinned = !$datas->pinned;
        } else {

            // Never pinned, now yes
            $datas->pinned = true;
        }

        // Save the file
        $json = json_encode($datas);
        file_put_contents($filepath, $json);

        echo json_encode($datas);
        break;

    case "archive":

        // Already archived once ?
        if (isset($datas->archived)) {

            // Invert
            $datas->archived = !$datas->archived;
        } else {

            // Never archived, now yes
            $datas->archived = true;
        }

        // Save the file
        $json = json_encode($datas);
        file_put_contents($filepath, $json);

        echo json_encode($datas);
        break;
    case "gettitle":

        echo $datas->title;
        die();
        break;

    case "getcontent":

        // Get raw content (without markdown)
        echo $datas->content;
        die();
        break;

    case "save":

        // Title and content to object
        $datas->title = $_POST["title"];
        $datas->content = $_POST["content"];

        // Last update is now
        $datas->modified = time();

        // Save the file
        $json = json_encode($datas);
        file_put_contents($filepath, $json);

        // Return formated content, but not saved in the file
        $datas->modified = date("d.m.Y H:i", $datas->modified);
        $datas->content = markdownReallyBasic($datas->content, $uid);

        // New content
        echo json_encode($datas);
        break;

    case "infos":

        // Return formated content, but not saved in the file
        $datas->modified = date("d.m.Y H:i", $datas->modified);
        $datas->content = markdownReallyBasic($datas->content, $uid);

        // New content
        echo json_encode($datas);
        break;

    case "delete":

        // Delete the file, the path is made with "datas/" hardcoded, so it "should" be secure
        unlink($filepath);

        echo json_encode($datas);
        break;

    case "background":

        // Colors to the object
        $datas->bgcolor = $_POST["bgcolor"];
        $datas->bgimage = $_POST["bgimage"];

        // Save the file
        $json = json_encode($datas);
        file_put_contents($filepath, $json);

        echo json_encode($datas);
        break;
}
