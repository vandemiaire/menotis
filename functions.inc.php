<?php

const SHOW_ARCHIVED = 1;
const SHOW_PINNED = 2;
const SHOW_OTHERS = 3;

const DEVICE_TYPE_TABLET = 1;
const DEVICE_TYPE_PHONE = 2;
const DEVICE_TYPE_COMPUTER = 3;

// Init
$notesdatas = [];

function guid()
{
    return bin2hex(random_bytes(16));
}

function isValidNoteUid($uid)
{
    // A note UID is the JSON filename without extension: "note" followed by letters/digits only.
    // This forbids "/", "\", "." and therefore any path traversal outside "datas/".
    return is_string($uid) && preg_match('/^note[a-zA-Z0-9]{1,64}$/', $uid) === 1;
}

function escHtml($text)
{
    // Escape any user text before putting it in HTML (prevents XSS)
    return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitizeBgColor($color)
{
    // Only "#RRGGBB" is allowed, anything else becomes "no color"
    return (is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1) ? $color : "";
}

function sanitizeBgImage($image)
{
    // Only an existing file of the "wallpapers" folder is allowed, anything else becomes "no wallpaper"
    if (!is_string($image) || preg_match('/^[a-zA-Z0-9_.-]+\.(jpg|png)$/i', $image) !== 1) {
        return "";
    }
    return is_file(__DIR__ . "/wallpapers/" . $image) ? $image : "";
}

function notesDatasLoad()
{

    // This function load all notes from the files and put them in the global array $notesdatas
    global $notesdatas;

    $sortingmax = 0;
    $countpinned = 0;
    $countarchived = 0;

    // Normal notes : NOT archived (pinned or not)
    $countnotarchived = 0;

    // All JSON files in data folder
    $files = glob("datas/*.json");
    foreach ($files as $file) {

        // Get file content
        $content = file_get_contents($file);

        // If content is valid
        if (false !== $content) {

            // Init
            $isthisnotepinned = false;
            $isthisnotearchived = false;

            // UID is the name of the file, without path and extension
            $uid = basename($file, ".json");

            // Json to object
            $datathisnote = json_decode($content);

            // Ignore invalid JSON and files that are not notes (e.g. odd names from a restored backup)
            if (!is_object($datathisnote) || !isValidNoteUid($uid)) {
                continue;
            }

            $datathisnote->uid = $uid;

            // Add json to array
            $notesdatas[] = $datathisnote;

            // Sorting should always be here, but meh...
            if (isset($datathisnote->sorting)) {
                if (intval($datathisnote->sorting) > $sortingmax) {
                    $sortingmax = intval($datathisnote->sorting);
                }
            }

            // Note pinned ?
            if (isset($datathisnote->pinned)) {
                $isthisnotepinned = ($datathisnote->pinned == true);
            }

            // Note archived ?
            if (isset($datathisnote->archived)) {
                $isthisnotearchived = ($datathisnote->archived == true);
                if ($isthisnotearchived) {
                    $countarchived++;
                } else {
                    $countnotarchived++;
                }
            } else {
                $countnotarchived++;
            }

            // If the note is pinned BUT not archived
            if (($isthisnotepinned) && (!$isthisnotearchived)) {
                $countpinned++;
            }
        }
    }

    // Sorting by "sorting" attribute
    usort($notesdatas, "notesDatasSort");

    // Sorting max
    $_SESSION["sortingmax"] = $sortingmax;
    $_SESSION["countpinned"] = $countpinned;
    $_SESSION["countarchived"] = $countarchived;
    $_SESSION["countnotarchived"] = $countnotarchived;
}

// Sorting function
function notesDatasSort($a, $b)
{
    return $a->sorting - $b->sorting;
}

function notesDataShow($show)
{

    global $notesdatas;

    $label_color = _("Change background");
    $label_edit = _("Edit this note");
    $label_delete = _("Delete this note");
    $label_modified = _("Last update");
    $label_upload = _("Upload a picture");
    $label_checkboxes_add = _("Add checkboxes");
    $label_checkboxes_remove = _("Remove checkboxes");

    if (empty($notesdatas)) {
        echo _("You currently have no notes.");
        return false;
    }

    // Loop on each note
    foreach ($notesdatas as $datathisnote) {

        $isthisnotepinned = false;
        $isthisnotearchived = false;

        // Note pinned ?
        if (isset($datathisnote->pinned)) {
            $isthisnotepinned = ($datathisnote->pinned == true);
        }

        // Note archived ?
        if (isset($datathisnote->archived)) {
            $isthisnotearchived = ($datathisnote->archived == true);
        }

        if (
            (($show == SHOW_PINNED) && $isthisnotepinned && !$isthisnotearchived) ||
            (($show == SHOW_OTHERS) && !$isthisnotepinned && !$isthisnotearchived) ||
            (($show == SHOW_ARCHIVED) && $isthisnotearchived)
        ) {

            // Background color and image
            $style = "";
            // Values are re-validated here because the JSON files may come from a restored backup
            $bgcolor = sanitizeBgColor($datathisnote->bgcolor ?? "");
            $bgimage = sanitizeBgImage($datathisnote->bgimage ?? "");
            if ($bgimage != "") {
                $style .= "background-size:cover;background-repeat:no-repeat;background-image:url(wallpapers/" . $bgimage . ") !important;";
            } else if ($bgcolor != "") {
                $style .= "background-color:" . $bgcolor . " !important;";
            }

            $modified = "";
            if (isset($datathisnote->modified)) {

                // Number of days between thes dates
                $date = new DateTime();
                $date->setTimestamp($datathisnote->modified);
                $today = new DateTime("today");

                $diff_days = daysBetweenTwoDates($today, $date);

                // Label according to the difference
                if ($diff_days === 0) {
                    $modified = sprintf(_("Today at %s"), $date->format(TIME_FORMAT));
                } elseif ($diff_days === 1) {
                    $modified = sprintf(_("Yesterday at %s"), $date->format(TIME_FORMAT));
                } else {
                    $modified = $date->format("d.m.Y " . TIME_FORMAT);
                }
            }

            // Pin icone
            if ($isthisnotepinned) {

                // Unpin icon
                $label_pin = _("Unpin this note");
            } else {

                // Pin icon
                $label_pin = _("Pin this note");
            }

            if ($isthisnotearchived) {

                $label_archived = _("Restore this note");

                // We hide some buttons
                $class_hide_when_archived = "d-none";
            } else {

                $label_archived = _("Archive this note");

                // Note NOT archived, we hide nothing
                $class_hide_when_archived = "";
            }

            // Labels and content (both escaped against XSS, markdown escapes internally)
            $datathisnote->title = escHtml($datathisnote->title ?? "");
            $datathisnote->content = markdownReallyBasic($datathisnote->content ?? "", $datathisnote->uid);

            $html = <<<HTML
                <div class="col col-md-2 col-6">
                    <div id="card-{$datathisnote->uid}" class="card" style="{$style}">
                        <div class="card-header p-2" data-uid="{$datathisnote->uid}">
                            <h5 class="card-title m-0" id="title-{$datathisnote->uid}">
                                {$datathisnote->title}
                            </h5>
                        </div>
                        <div class="card-body py-2 py-md-2 px-2 px-md-3">
                            <div class="search-here card-text" id="content-{$datathisnote->uid}" data-uid="{$datathisnote->uid}">
                                {$datathisnote->content}
                            </div>
                        </div>
                        <div class="card-footer px-1 py-0">
                            <div class="row p-1 m-0 opacity-25 opacity-1-when-hover">
                                <div class="col text-start align-middle p-0 m-0" data-bs-toggle="tooltip" title="{$label_modified}">
                                    <small id="modified-{$datathisnote->uid}">{$modified}</small>
                                </div>
                                <div class="col-2 text-end align-middle p-0">
                                    <div class="dropdown">
                                    <a class="menu-ellipsis dropdown-link link-underline link-underline-opacity-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="ellipsis-vertical.svg.php" style="height:20px;margin-bottom:2px;" />
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-pin dropdown-item {$class_hide_when_archived}" href="#">{$label_pin}</a></li>
                                        <li><a id="btn-color-{$datathisnote->uid}" data-uid="{$datathisnote->uid}" data-bgcolor="{$bgcolor}" data-bgimage="{$bgimage}" class="note-btn-modal-color dropdown-item {$class_hide_when_archived}" href="#">{$label_color}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-modal-upload-image dropdown-item {$class_hide_when_archived}" href="#">{$label_upload}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-archive dropdown-item" href="#">{$label_archived}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-modal-delete dropdown-item" href="#">{$label_delete}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-modal-edit dropdown-item {$class_hide_when_archived}" href="#">{$label_edit}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-checkboxes-add dropdown-item {$class_hide_when_archived}" href="#" class="dropdown-item">{$label_checkboxes_add}</a></li>
                                        <li><a data-uid="{$datathisnote->uid}" class="note-btn-checkboxes-remove dropdown-item {$class_hide_when_archived}" href="#" class="dropdown-item">{$label_checkboxes_remove}</a></li>
                                    </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                HTML;

            echo $html;

            // End the loop on each card
        }
    }

    return true;
}

function getDeviceType()
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (preg_match('/tablet|ipad/', $ua)) {
        return DEVICE_TYPE_TABLET;
    }
    if (preg_match('/mobile|android|iphone|ipod/', $ua)) {
        return DEVICE_TYPE_PHONE;
    }
    return DEVICE_TYPE_COMPUTER;
}

function daysBetweenTwoDates(DateTimeInterface $d1, DateTimeInterface $d2): int
{
    // On ignore l'heure
    $date1 = new DateTime($d1->format('Y-m-d'));
    $date2 = new DateTime($d2->format('Y-m-d'));

    return $date1->diff($date2)->days;
}

function markdownReallyBasic($text, $uid)
{

    // Escape ALL user text first (prevents XSS), the markdown below only adds our own tags.
    // Markdown characters (# * _ [ ] ! ( )) are not changed by htmlspecialchars.
    // "\0" is reserved for the internal placeholders of markdownInline().
    $text = escHtml(str_replace("\0", "", (string)$text));

    // Checkbox counter, shared by all lines. It MUST count every "[ ]", "[x]", "[X]" of the raw text
    // in order (even inside code lines or image alt texts) because "checkbox.ajax.php" (action "check")
    // finds the box to toggle with the same count.
    $index = 0;

    // One line at a time: works with "\n" and "\r\n" (Windows) line endings, and bold/italic never
    // spread over several lines.
    $lines = preg_split('/\R/', $text);
    foreach ($lines as &$line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            $line = '';
            continue;
        }

        // Code line (starts with ">", already escaped as "&gt;"): shown as is, no formatting
        if (preg_match('/^&gt;\s*(.+)$/', $trimmed, $m)) {
            $index += preg_match_all('/\[(x|X| )\]/', $m[1]);
            $line = '<div class="code lh-sm my-0">' . $m[1] . '</div>';
            continue;
        }

        // Titles: "#" => h3, "##" => h4, "###" => h5 (no <p> around them)
        if (preg_match('/^(#{1,3})[ \t]+(.+)$/', $trimmed, $m)) {
            $level = strlen($m[1]) + 2;
            $line = "<h{$level} class=\"lh-sm\">" . markdownInline($m[2], $uid, $index) . "</h{$level}>";
            continue;
        }

        // Normal line -> <p>
        $line = "<p class=\"lh-sm my-1\">" . markdownInline($trimmed, $uid, $index) . "</p>";
    }

    return implode("\n", $lines);
}

function markdownInline($text, $uid, &$index)
{
    // Formatting inside ONE line of already escaped text: images, URLs, checkboxes, bold, italic.

    // Images, URLs and checkboxes are replaced first by placeholders ("\0" + number + "\0"),
    // so that bold/italic can never break them (e.g. "_" in "https://site.com/my_page_x").
    $tokens = [];
    $url = 'https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:&amp;|[-a-zA-Z0-9()@:%_\+.~#?\/=])*';
    $text = preg_replace_callback(
        '/(?<image>!\[(?<alt>[^\]]*)\]\((?<src>[^)\s]+)\))|(?<url>' . $url . ')|\[(?<box>x|X| )\]/',
        function ($m) use (&$tokens, &$index, $uid) {
            if (isset($m["box"]) && $m["box"] !== "") {

                // Checkbox
                $checked = $m["box"] !== ' ' ? ' checked' : '';
                $html = '<input data-uid="' . $uid . '" type="checkbox" class="form-check-input md-checkbox" data-index="' . ($index++) . '"' . $checked . '>';
            } elseif (isset($m["url"]) && $m["url"] !== "") {

                // Clickable URL
                $html = '<a href="' . $m["url"] . '" target="_blank" class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover">' . $m["url"] . '</a>';
            } else {

                // Image ![alt](file), a "[x]" alt text still counts as a checkbox (see markdownReallyBasic)
                $index += preg_match_all('/\[(x|X| )\]/', $m["image"]);
                $html = '<img src="' . $m["src"] . '" alt="' . $m["alt"] . '" class="img-thumbnail my-1" data-bs-toggle="tooltip" title="' . $m["alt"] . '" onclick="return imageView(this);">';
            }
            $tokens[] = $html;
            return "\0" . (count($tokens) - 1) . "\0";
        },
        $text
    );

    // No space just inside the markers, so "2 * 3 * 4" or "a _ b _ c" stay as is

    // **bold**, then *bold* (avoiding the ones already done)
    $text = preg_replace('/\*\*(?!\s)(.+?)(?<!\s)\*\*/u', '<b>$1</b>', $text);
    $text = preg_replace('/(?<!\*)\*(?![\*\s])(.+?)(?<![\*\s])\*(?!\*)/u', '<b>$1</b>', $text);

    // _italic_ (not inside a word, so "my_variable_name" stays as is)
    $text = preg_replace('/(?<![\p{L}\p{N}_])_(?![_\s])(.+?)(?<![_\s])_(?![\p{L}\p{N}_])/u', '<i>$1</i>', $text);

    // Put back images, URLs and checkboxes
    return preg_replace_callback('/\0(\d+)\0/', fn($m) => $tokens[(int)$m[1]], $text);
}
