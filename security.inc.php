<?php

function checkCookie()
{
    // Check the cookie for auth
    if (isset($_COOKIE[COOKIE_NAME])) {
        if ($_COOKIE[COOKIE_NAME] == COOKIE_VALUE) {

            // Re-set cookie
            cookieSet();

            return true;
        }
    }

    // Fallback
    return false;
}

function logout()
{
    // Remove cookie (path "/" used now, and the default path used by older versions)
    setcookie(COOKIE_NAME, "", cookieOptions(1));
    setcookie(COOKIE_NAME, "", 1);

    // Clean memory
    if (isset($_COOKIE[COOKIE_NAME])) {
        unset($_COOKIE[COOKIE_NAME]);
    }
}

function checkLogin($login, $password)
{

    // Checking login and password
    if (($login == LOGIN) && (sha1($password) == PASSWORD)) {

        // We are logged
        cookieSet();

        return true;
    }

    // Fallback
    return false;
}

function cookieSet()
{

    // We are logged
    setcookie(COOKIE_NAME, COOKIE_VALUE, cookieOptions(time() + (86400 * COOKIE_RETENTION)));
}

function isHttps()
{
    return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");
}

function cookieOptions($expires)
{
    return [
        "expires" => $expires,
        "path" => "/",
        "secure" => isHttps(),   // Only sent over HTTPS (when the site uses HTTPS)
        "httponly" => true,      // Not readable by JavaScript (limits damage of an XSS)
        "samesite" => "Strict",  // Never sent by requests coming from another site (CSRF)
    ];
}
