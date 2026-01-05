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
    // Remove cookie
    setcookie(COOKIE_NAME, "", 1, "/");

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
    setcookie(COOKIE_NAME, COOKIE_VALUE, time() + (86400 * COOKIE_RETENTION));
}
