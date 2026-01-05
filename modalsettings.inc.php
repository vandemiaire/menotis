<div id="modal-settings" class="modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Settings") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col">
                            <label for="timezone-select-settings" class="form-label"><?= _("Timezone") ?></label>
                            <select class="form-select" id="timezone-select-settings">
                            </select>
                        </div>
                        <div class="col">
                            <label for="language-select-settings" class="form-label"><?= _("Language") ?></label>
                            <select class="form-select" id="language-select-settings">
                            </select>
                        </div>
                        <div class="col">
                            <label for="time-format-select-settings" class="form-label"><?= _("Time format") ?></label>
                            <select class="form-select" id="time-format-select-settings">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row">
                        <div class="col">
                            <label for="login-settings" class="form-label"><?= _("Login") ?></label>
                            <input type="text" class="form-control" id="login-settings">
                        </div>
                        <div class="col">
                            <label for="password-settings" class="form-label"><?= _("Password") ?></label>
                            <input type="password" class="form-control" id="password-settings">
                            <div class="form-text"><?= _("Leave blank to avoid changing the password.") ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Close without saving") ?>"><?= _("Close") ?></button>
                <button type="button" class="btn btn-primary" id="modal-settings-save" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Save") ?>"><?= _("Save") ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("modal-settings-save").addEventListener("click", async () => {
        const locale = document.getElementById("language-select-settings").value;
        const timeformat = document.getElementById("time-format-select-settings").value;
        const timezone = document.getElementById("timezone-select-settings").value;
        const login = document.getElementById("login-settings").value;
        const password = document.getElementById("password-settings").value;

        try {
            const response = await postData("settings.ajax.php", {
                action: "save",
                locale,
                timezone,
                login,
                password,
                timeformat
            }, "json");

            if (response.success) {
                toastSuccess("You must reload the page");
            } else {
                toastError(response.message || "Erreur lors de la sauvegarde.");
            }

        } catch (err) {
            console.error(err);
            toastError("Erreur réseau ou serveur.");
        }
    });
</script>