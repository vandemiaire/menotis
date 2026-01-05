<?php

session_start();

// Includes without HTML or JS
include("const.inc.php");
include("functions.inc.php");
include("security.inc.php");

// Archive mode
$archivemode = false;
if (isset($_GET["archives"])) {
    $archivemode = true;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="" style="font-size:0.9rem">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="manifest.json">
    <link href="style.css" rel="stylesheet">
    <link href="bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/bootstrap.bundle.min.js"></script>
    <script src="functions.js"></script>
    <script src="theme.js"></script>
    <title><?= APP_NAME ?></title>
</head>

<?php

// Does login and password exists ?
if (!file_exists("datas/config.php")) {

    // Nope, create them.
    include("firstlaunch.php");
    die();
} else {

    // Yes, use them.
    include("datas/config.php");
}

// Logged user ?
if (!checkCookie()) {
    include("login.php");
    die();
}

// Load and sort all the notes
notesDatasLoad();

?>

<body class="p-0">

    <nav class="navbar sticky-top navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <img src="menotis_icon_512.png" style="height:24px;" />&nbsp;
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    if (!$archivemode) {
                    ?>
                        <li class="nav-item mx-2">
                            <a href="#" class="nav-link active" id="link-add-note">
                                <?= _("Add note") ?>
                            </a>
                        </li>
                    <?php
                    }
                    ?>
                    <li class="nav-item mx-2">
                        <?php
                        if ($archivemode) {
                            $label = _("Actives notes");
                            $count = $_SESSION["countnotarchived"];
                            $link = "?";
                        } else {
                            $label = _("Archived notes");
                            $count = $_SESSION["countarchived"];
                            $link = "?archives=1";
                        }
                        echo <<<HTML
                        <a href="{$link}" class="nav-link active">
                            {$label}
                            <span class="badge text-bg-secondary">{$count}</span>
                        </a>
                        HTML;
                        ?>
                    </li>
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= _("Options") ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" class="dropdown-item" id="link-modal-images">
                                    <?= _("Images") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-modal-backup">
                                    <?= _("Backup") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-theme">
                                    <?= _("Switch theme") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-modal-settings">
                                    <?= _("Settings") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-logout">
                                    <?= _("Logout") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-help" data-bs-toggle="modal" data-bs-target="#modal-help">
                                    <?= _("Help") ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" id="link-about">
                                    <?= _("About") ?>
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input id="search-box" class="form-control me-2" type="search" placeholder="<?= _("Search") ?>" aria-label="Search" />
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-3">

        <?php

        $row = "row g-2 g-lg-4";

        if ($archivemode) {

            $label = _("Archived notes");

            // Not pinned
            echo "<h4>" . $label . "</h4>";
            echo "<div class=\"" . $row . "\">";
            notesDataShow(SHOW_ARCHIVED);
            echo "</div>";
        } else {
            // Do we have pinned notes ?
            if (intval($_SESSION["countpinned"]) > 0) {

                $label = _("Pinned notes");

                // Yes, show the pinned category
                echo "<h4>" . $label . "</h4>";
                echo "<div class=\"" . $row . "\">";
                notesDataShow(SHOW_PINNED);
                echo "</div>";

                $label = _("Others notes");

                // Show the others notes category only if pinned notes
                echo "<h4 class=\"mt-4\">" . $label . "</h4>";
            }


            echo "<div class=\"" . $row . "\">";
            notesDataShow(SHOW_OTHERS);
            echo '</div>';
        }

        ?>

    </div>
    </div>

    <?php

    // Includes containing HTML or JS
    include("toasts.inc.php");
    include("modaledit.inc.php");
    include("modaldelete.inc.php");
    include("modalcolor.inc.php");
    include("modaluploadimage.inc.php");
    include("modalimages.inc.php");
    include("modalbackup.inc.php");
    include("modalhelp.inc.php");
    include("modalabout.inc.php");
    include("modalsettings.inc.php");

    ?>

    <script src="events.js"></script>
    <input type="hidden" id="currentuid" />

</body>

</html>