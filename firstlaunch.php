<?php

// Big security here : if the config file already exists then it's maybe a hacking attempt, someone
// trying to call the "firstlaunch.php" manually.
if (file_exists("datas/config.php")) {
    die("Nice try...");
}

// ****************************************************************************
// PHP EXTENSIONS
// ****************************************************************************

$required_extension = ["fileinfo", "gettext", "zip"];
$missing = [];

foreach ($required_extension as $extension) {
    if (!extension_loaded($extension)) {
        $missing[] = $extension;
    }
}

if (!empty($missing)) {

    $missing_text = implode(", ", $missing);

    echo <<<HTML
    <!doctype html>
    <body>
    <div class="alert alert-danger" role="alert">
        You have to activate the following PHP extensions : {$missing_text}<br />
        Edit your "php.ini" file, then activate the line "extension=..." (remove the first ";") for each one.<br />
        Then, restart your Webserver.
    </div>
    </body>
    </html>
    HTML;

    die();
}

// ****************************************************************************
// DATAS FOLDER WRITABLE
// ****************************************************************************

$writing_test_file = "datas/.test_write";
$data_folder_writable = false;

// Writing tests
if (@file_put_contents($writing_test_file, "test") !== false) {

    // Writing OK
    $data_folder_writable = true;
    unlink($writing_test_file);
}

// Fatal error
if (!$data_folder_writable) {

    echo <<<HTML
    <!doctype html>
    <body>
    <div class="alert alert-danger" role="alert">
        You must first grant write permissions to the "datas" directory, this is currently not the case.
    </div>
    </body>
    </html>
    HTML;

    die();
}

// ****************************************************************************
// FIRST LAUNCH FORM
// ****************************************************************************

include("toasts.inc.php");

?>

<body>

    <div class="card mt-5 mx-auto" style="width:350px;">
        <div class="card-header"><?= sprintf(_("%s installation"), APP_NAME) ?></div>
        <div class="card-body">
            <p>
                <?= _("This is your first launch, please create your account.") ?>
            </p>
            <form>
                <div class="mb-3">
                    <label for="login" class="form-label"><?= _("Login") ?></label>
                    <input type="text" class="form-control" id="login">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><?= _("Password") ?></label>
                    <input type="password" class="form-control" id="password">
                    <div id="password-message" class="form-text"><?= _("Choose a strong password") ?></div>
                </div>
                <div class="mb-3">
                    <label for="password2" class="form-label"><?= _("Re-enter your password") ?></label>
                    <input type="password" class="form-control" id="password2">
                </div>
                <div class="mb-3">
                    <label for="language-select" class="form-label"><?= _("Language") ?></label>
                    <select class="form-select" id="language-select-firstlaunch">
                    </select>
                </div>
                <div class="mb-3">
                    <label for="timezone-select" class="form-label"><?= _("Timezone") ?></label>
                    <select class="form-select" id="timezone-select-firstlaunch">
                    </select>
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <button id="firstlaunch-button" type="submit" class="btn btn-primary"><?= _("Create the account") ?></button>
        </div>
    </div>

</body>

<script>
    const passwordInput = document.getElementById("password");
    const message = document.getElementById("password-message");

    // Load timezones and locales for first launch
    (async () => {
        const tz = await postData("settings.ajax.php", {
            action: "gettimezones",
            default: "Europe/Zurich"
        }, "text");
        document.getElementById("timezone-select-firstlaunch").innerHTML = tz;

        const loc = await postData("settings.ajax.php", {
            action: "getlocales",
            default: "en_GB"
        }, "text");
        document.getElementById("language-select-firstlaunch").innerHTML = loc;
    })();

    // Listen for changes in the password input field
    passwordInput.addEventListener("input", function() {

        // Get the current value of the password
        const password = passwordInput.value;

        // Check password strength with multiple conditions:
        // - At least 8 characters
        // - Contains an uppercase letter
        // - Contains a lowercase letter
        // - Contains a digit
        // - Contains a special character
        const isStrong =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[a-z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[^A-Za-z0-9]/.test(password);

        // If password does not meet all criteria, show warning message in red
        if (!isStrong) {
            message.textContent = "Password too weak (8 characters, uppercase, lowercase, number, special character).";
            message.style.color = "red";
        } else {
            // If password is strong enough, show confirmation message in green
            message.textContent = "Password strong enough.";
            message.style.color = "green";
        }
    });

    // First launch user creation
    document.getElementById("firstlaunch-button").addEventListener("click", async () => {

        // Get the login input value
        const login = document.getElementById("login").value;

        // Get the password input value
        const password = document.getElementById("password").value;

        // Get the password confirmation input value
        const password2 = document.getElementById("password2").value;

        // Validate login: show error toast if empty
        if (!login) return toastError("You must enter a login.");

        // Validate password: show error toast if empty
        if (!password) return toastError("You must enter a password.");

        // Validate that both passwords match
        if (password !== password2) return toastError("Your two passwords do not match.");

        // Send POST request to create a new user with login, password, locale, and timezone
        const data = await postData("security.ajax.php", {
            action: "new",
            login,
            password,
            locale: document.getElementById("language-select-firstlaunch").value, // get selected locale
            timezone: document.getElementById("timezone-select-firstlaunch").value // get selected timezone
        });

        // If the server confirms authentication is successful, reload the page
        if (data.auth === true) {
            window.location.reload();
        } else {
            // Otherwise, show error message returned from server
            toastError(data.message);
        }
    });
</script>