<div id="modal-color" class="modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Color") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    <label for="modal-background-value" class="form-label"><?= _("Choose a color") ?></label>
                </div>
                <p id="modal-color-palettes" class="bg-light p-2 text-center rounded-2">
                </p>
                <p>
                    <label for="modal-color-value" class="form-label"><?= _("Change it if you want") ?></label>
                    <input type="color" class="form-control form-control-color" id="modal-color-value" value="#563d7c" style="width:100%;">
                </p>
                <p>
                    <label for="modal-background-value" class="form-label"><?= _("Or select a wallpaper") ?></label>
                    <select class="form-select" aria-label="Default select example" id="modal-background-value">
                        <option value="" selected><?= _("No wallpaper") ?></option>

                        <?php

                        // Loop on each wallpapaer
                        $files = glob('wallpapers/*.{jpg,png}', GLOB_BRACE);
                        foreach ($files as $file) {

                            // Extract filename infos
                            $path_parts = pathinfo($file);

                            // Select option
                            echo "<option value=\"" . $path_parts['basename'] . "\">" . $path_parts['filename'] . "</option>";
                        }
                        ?>

                    </select>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Close without saving") ?>"><?= _("Close") ?></button>
                <button type="button" class="btn btn-warning" id="modal-color-default" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Remove the color of this note") ?>"><?= _("Reset") ?></button>
                <button type="button" class="btn btn-primary" id="modal-color-yes" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Use this color") ?>"><?= _("Save") ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    // Appliquer la couleur sélectionnée
    document.getElementById("modal-color-yes").addEventListener("click", async () => {
        const uid = document.getElementById("currentuid").value;
        const bgcolor = document.getElementById("modal-color-value").value;
        const bgimage = document.getElementById("modal-background-value").value;

        await postData("notes.ajax.php", {
            action: "background",
            uid,
            bgcolor,
            bgimage
        });

        const card = document.getElementById("card-" + uid);
        card.style.setProperty("background-color", bgcolor, "important");

        if (bgimage) {
            card.style.setProperty("background-image", "url(wallpapers/" + bgimage + ")", "important");
        } else {
            card.style.removeProperty("background-image");
        }

        const btnColor = document.getElementById("btn-color-" + uid);
        btnColor.dataset.bgcolor = bgcolor;
        btnColor.dataset.bgimage = bgimage;
    });

    // Réinitialiser couleur par défaut
    document.getElementById("modal-color-default").addEventListener("click", async () => {
        const uid = document.getElementById("currentuid").value;

        await postData("notes.ajax.php", {
            action: "background",
            uid,
            bgcolor: "",
            bgimage: ""
        });

        const card = document.getElementById("card-" + uid);
        card.style.backgroundColor = "";
        card.style.backgroundImage = "";

        const btnColor = document.getElementById("btn-color-" + uid);
        btnColor.dataset.bgcolor = "";
        btnColor.dataset.bgimage = "";
    });
</script>