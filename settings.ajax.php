<?php
include("csrf.inc.php");

include("const.inc.php");

$config_file = "datas/config.php";

switch ($_POST["action"]) {
    case "save":

        include($config_file);
        include("security.inc.php");

        // Hacking attempt
        if (!checkCookie()) {
            echo "No rights...";
            die();
        }

        $timezone = $_POST["timezone"];
        $locale = $_POST["locale"];
        $login = trim($_POST["login"]);
        $timeformat = $_POST["timeformat"];
        $password = trim($_POST["password"]);

        // Line by line
        $lignes = file($config_file);

        // Loop on each line
        foreach ($lignes as &$ligne) {

            // New timeformat
            if (preg_match('/^\s*const\s+TIME_FORMAT\b/', $ligne)) {
                $ligne = "\tconst TIME_FORMAT=\"" . $timeformat . "\";\n";
            }

            // New timezone
            if (preg_match('/^\s*const\s+TIME_ZONE\b/', $ligne)) {
                $ligne = "\tconst TIME_ZONE=\"" . $timezone . "\";\n";
            }

            // New locale
            if (preg_match('/^\s*const\s+LOCALE\b/', $ligne)) {
                $ligne = "\tconst LOCALE=\"" . $locale . "\";\n";
            }

            // New login
            if (($login != "") && (preg_match('/^\s*const\s+LOGIN\b/', $ligne))) {
                $ligne = "\tconst LOGIN=\"" . $login . "\";\n";
            }

            // New password
            if (($password != "") && (preg_match('/^\s*const\s+PASSWORD\b/', $ligne))) {
                $ligne = "\tconst PASSWORD=\"" . sha1($password) . "\";\n";
            }
        }

        // Réécrire le fichier
        file_put_contents($config_file, implode('', $lignes));

        // Force all these new values
        date_default_timezone_set($timezone);
        putenv("LC_ALL=" . $locale);
        setlocale(LC_ALL, $locale);
        bindtextdomain("messages", "./locale");
        textdomain("messages");

        $datas = new stdClass();
        $datas->success = "OK";
        echo json_encode($datas);
        die();
        break;

    case "getlocales":

        // No security here, because this script is also called on first launch

        if (file_exists($config_file)) {
            include($config_file);
            $config_locale = LOCALE;
        } else {
            $config_locale = "en_GB";
        }

        $default = $_POST["default"];
        $locales = ["en_GB" => "English", "fr_FR" => "Français"];

        // Default value
        if ($default == "") {
            $default = $config_locale;
        }

        foreach ($locales as $locale => $description) {
            echo "<option value=\"" . $locale . "\" " . (($locale == $default) ? "selected" : "") . ">" . $description . "</option>";
        }

        die();
        break;

    case "gettimezones":

        // No security here, because this script is also called on first launch

        if (file_exists($config_file)) {
            include($config_file);
        }

        $default = $_POST["default"];

        // Default value
        if ($default == "") {
            $default = date_default_timezone_get();
        }

        $timezones = DateTimeZone::listIdentifiers();
        foreach ($timezones as $tz) {
            $safeTz = htmlspecialchars($tz, ENT_QUOTES, 'UTF-8');
            $selected = ($tz === $default) ? " selected" : "";
            echo "<option value=\"$safeTz\"$selected>$safeTz</option>";
        }

        die();
        break;

    case "gettimeformats":

        if (file_exists($config_file)) {
            include($config_file);
        }

        // All time formats
        $time_formats = ["H:i" => _("24 hours"), "h:i A" => _("12 hours")];

        // Prepare for SELECT tag
        foreach ($time_formats as $time_format => $description) {
            $selected = ($time_format === TIME_FORMAT) ? " selected" : "";
            echo "<option value=\"$time_format\"$selected>$description</option>";
        }

        die();
        break;

    case "getlogin":

        if (file_exists($config_file)) {
            include($config_file);
            echo LOGIN;
            die();
        } else {
            // Nothing
        }

        break;
}
