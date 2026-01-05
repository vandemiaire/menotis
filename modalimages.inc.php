<div id="modal-images" class="modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Images") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= _("The images will be deleted without confirmation, you will then need to delete the markdown links in your notes that used them.") ?>
                </p>
                <div class="row text-center" id="modal-images-list">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal-image-single" class="modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img id="image-single" class="img-fluid rounded" data-bs-dismiss="modal" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
            </div>
        </div>
    </div>
</div>
