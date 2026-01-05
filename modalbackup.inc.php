<form id="form-upload-backup">
    <div id="modal-backup" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= _("Backup or restore") ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        <?= _("You could manually schedule a backup on your server, just save the whole \"datas\" folder somewhere, it contains all your notes and images.") ?>
                    </p>
                    <p>
                        <?= _("Or, you can backup your notes and images to a ZIP file, and restore it here.") ?>
                    </p>
                    <h5 class="mt-5">
                        <?= _("Backup your notes and images") ?>
                    </h5>
                    <p>
                        <?= _("All your notes and images will be saved in a ZIP file.") ?>
                    </p>
                    <p>
                        <button id="btn-backup" type="button" class="btn btn-primary"><?= _("Backup") ?></button>
                    </p>
                    <h5 class="mt-5">
                        <?= _("Restore your notes and images") ?>
                    </h5>
                    <p>
                        <?= _("The notes from the backup will be merged with the existing notes.") ?>
                    </p>
                    <p>
                        <input class="form-control" type="file" id="upload-file-backup">
                    <div id="upload-backup-help" class="form-text"><?= _("Use the ZIP file created with the backup button") ?></div>
                    </p>
                    <p>
                        <button id="btn-restore" type="submit" class="btn btn-primary"><?= _("Restore") ?></button>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById("btn-backup").addEventListener("click", async () => {

        try {
            const response = await fetch("backup.ajax.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    action: "backup"
                })
            });

            const data = await response.json();

            if (data.error) {
                toastError(data.error);
            } else {
                // Initiate download
                const link = document.createElement("a");
                link.href = data.filepath;
                link.download = data.filename || ""; // nom optionnel
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

        } catch (err) {
            console.error("Erreur lors du backup :", err);
            toastError("Erreur lors du backup.");
        }
    });


    document.getElementById("form-upload-backup").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData();
        const fileInput = document.getElementById("upload-file-backup");

        if (fileInput.files.length === 0) {
            alert("No file selected");
            return;
        }

        // Add file and UID
        formData.append("uploadfile", fileInput.files[0]);
        formData.append("action", "restore");

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "backup.ajax.php", true);
        xhr.onload = function() {
            if (xhr.status === 200) {

                // Refresh everything
                window.location.reload();

            } else {
                var elem = document.getElementById("upload-backup-help");
                elem.innerHTML = xhr.responseText;
                elem.classList.add("text-danger");
            }
        };

        xhr.send(formData);
    });
</script>