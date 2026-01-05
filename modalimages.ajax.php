<?php

include("const.inc.php");
include("datas/config.php");
include("security.inc.php");

// Hacking attempt
if (!checkCookie()) {
    die();
}

foreach (glob("datas/*.{jpg,JPG,jpeg,JPEG,png,PNG}", GLOB_BRACE) as $file) {

    // Just the file name with extension (it can be "png" or "jpg")
    $basename = basename($file);

    // Label
    $label_delete = _("Delete");

    // We have to use "onclick" in this case (and not "click" in "event.js") because this HTML code is generated
    // and not ready when the DOM is loaded.
    echo <<<HTML
    <div class="col">
    <div>
        <img src="{$file}" class="img-thumbnail" style="height:200px;">
    </div>
    <div class="text-center">
        <button data-basename="{$basename}" type="button" onclick="return imageDelete(this);" class="btn btn-danger my-2">{$label_delete}</button>
    </div>
    </div>
    HTML;
}
