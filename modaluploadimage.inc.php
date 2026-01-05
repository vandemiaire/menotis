<form id="form-upload-image">
    <div id="modal-upload-image" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= _("Upload a picture") ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= _("A markdown code including the image will be inserted into the note.") ?></p>
                    <div class="mb-3">
                        <label for="upload-title" class="form-label"><?= _("Title") ?> (<?= _("optionnal") ?>)</label>
                        <input id="upload-title" type="text" class="form-control" placeholder="<?= _("Title") ?>">
                    </div>
                    <div class="mb-3">
                        <label for="upload-file" class="form-label"><?= _("Image file") ?></label>
                        <input class="form-control" type="file" id="upload-file-image">
                        <div id="upload-image-help" class="form-text"><?= _("Authorized formats: jpg, png") ?></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                    <button type="submit" class="btn btn-primary"><?= _("Upload") ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById("form-upload-image").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData();
        const fileInput = document.getElementById("upload-file-image");
        const uid = document.getElementById("currentuid").value;
        const title = document.getElementById("upload-title").value;

        if (fileInput.files.length === 0) {
            alert("No file selected");
            return;
        }

        // Add file and UID
        formData.append("uploadfile", fileInput.files[0]);
        formData.append("uid", uid);
        formData.append("title", title);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "uploadimage.ajax.php", true);
        xhr.onload = function() {
            if (xhr.status === 200) {

                // Refresh the note
                noteRefresh(uid);

                // Close modal
                var myModalEl = document.getElementById("modal-upload-image");
                var modal = bootstrap.Modal.getInstance(myModalEl)
                modal.hide();

            } else {
                var elem = document.getElementById("upload-image-help");
                elem.innerHTML = xhr.responseText;
                elem.classList.add("text-danger");
            }
        };

        xhr.send(formData);
    });
</script>