<div id="modal-edit" class="modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Edit") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edittitle" class="form-label"><?= _("Title") ?> (<?= _("optionnal") ?>)</label>
                    <input id="edittitle" type="text" class="form-control" placeholder="<?= _("Title") ?>">
                </div>
                <div class="mb-3">
                    <label for="editcontent" class="form-label"><?= _("Content") ?></label>
                    <textarea id="editcontent" class="form-control" rows="15" placeholder="<?= _("Content") ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Close without saving") ?>"><?= _("Close") ?></button>
                <button type="button" class="btn btn-primary" id="modal-edit-save" data-bs-dismiss="modal" data-bs-toggle="tooltip" title="<?= _("Save this note") ?>"><?= _("Save") ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("modal-edit-save").addEventListener("click", async () => {
        const uid = document.getElementById("currentuid").value;
        const title = document.getElementById("edittitle").value;
        const content = document.getElementById("editcontent").value;

        if (!content) return toastError("Please specify a content");

        const data = await postData("notes.ajax.php", {
            action: "save",
            uid,
            title,
            content
        });

        if (uid === "") {
            window.location.reload();
        } else {
            document.getElementById("title-" + uid).innerHTML = data.title;
            document.getElementById("content-" + uid).innerHTML = data.content;
            document.getElementById("modified-" + uid).innerHTML = data.modified;

        }
    });
</script>