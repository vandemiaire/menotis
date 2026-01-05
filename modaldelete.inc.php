<div id="modal-delete" class="modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Delete") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= _("Do you really want to permanently delete this note ?") ?>
                </p>
                <p class="text-danger">
                    <?= _("This operation is irreversible !") ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("No") ?></button>
                <button type="button" class="btn btn-danger" id="modal-delete-yes" data-bs-dismiss="modal"><?= _("Yes") ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("modal-delete-yes").addEventListener("click", () => {

        const uid = document.getElementById("currentuid").value;

        // Remove file via POST
        fetch("notes.ajax.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                action: "delete",
                uid: uid
            })
        });

        // Remove from DOM
        const card = document.getElementById(uid + "-card");
        if (card) card.remove();

    });
</script>