<?php

// Protection against CSRF (another website making your browser call Ménotis).
// Must be the first include of every "*.ajax.php" file.
//
// Every AJAX call of Ménotis sends the custom header "X-Menotis: 1" (see postData() in "functions.js").
// A foreign website cannot add a custom header to a cross-site request without a CORS
// authorization that Ménotis never gives, so such requests are rejected here.

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST" || ($_SERVER["HTTP_X_MENOTIS"] ?? "") !== "1") {
    http_response_code(403);
    exit("Forbidden");
}
