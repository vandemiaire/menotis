<?php
include("csrf.inc.php");
include("const.inc.php");

// Init
$datas = new stdClass();
$datas->auth = false;
$datas->message = "";

// Login and pasword file
$config_file = "datas/config.php";

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "new":

            // Big security here : if the config file already exists then it's maybe a hacking attempt, someone
            // trying to call this script manually.
            if (file_exists($config_file)) {
                $datas->message = "Nice try...";
                echo json_encode($datas);
                die();
            }

            // Get the post values
            $login = $_POST["login"];
            $password = $_POST["password"];
            $locale = $_POST["locale"];
            $timezone = $_POST["timezone"];

            // Must not contain ' or "
            if (!strpbrk($login, "'\"") === false) {
                $datas->message = "Your login must not contain an apostrophe or quotation marks.";
                echo json_encode($datas);
                die();
            }
            if (!strpbrk($password, "'\"") === false) {
                $datas->message = "Your password must not contain an apostrophe or quotation marks.";
                echo json_encode($datas);
                die();
            }

            // Name of the cookie, random for more security
            $cookie_name = strtolower(uniqid("menotis"));

            // Cookie value, must be random
            $cookie_value = bin2hex(random_bytes(16));

            // Hash the password
            $password = sha1($password);

            // Create the config file
            $config_content = <<<CONFIG
            <?php
                const LOGIN="{$login}";
                const PASSWORD="{$password}";
                const LOCALE="{$locale}";
                const COOKIE_NAME="{$cookie_name}";
                const COOKIE_VALUE="{$cookie_value}";
                const TIME_ZONE="{$timezone}";
                const TIME_FORMAT="H:i";
                date_default_timezone_set(TIME_ZONE);
                putenv("LC_ALL=" . LOCALE);
                setlocale(LC_ALL, LOCALE);
                bindtextdomain("messages", "./locale");
                textdomain("messages");
            ?>
            CONFIG;

            // Write the config file
            file_put_contents($config_file, $config_content);

            if (file_exists($config_file)) {

                // All good
                $datas->auth = true;
            } else {

                // Something went wrong
                $datas->message = "Unable to create a file in the \"datas\" directory, ensure that write permissions are granted on this directory.";
            }

            break;

        case "login":

            // Login and password file
            include($config_file);
            include("security.inc.php");

            // Default
            $datas->message = _("Wrong login or password");

            if ((isset($_POST["login"])) && (isset($_POST["password"]))) {

                if (checkLogin($_POST["login"], $_POST["password"])) {

                    // Returned values
                    $datas->auth = true;
                    $datas->message = _("You are now logged in");
                }
            }

            break;

        case "logout":

            // Login and password file
            include($config_file);
            include("security.inc.php");

            // Returned values
            $datas->message = _("You are now logged out");

            logout();

            break;
    }
}

// Return JSON
echo json_encode($datas);
