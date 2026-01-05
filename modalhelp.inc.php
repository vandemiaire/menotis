<div id="modal-help" class="modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _("Help") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h3>Markdown</h3>
                <p><?= _("A basic markdown is available in the notes editor.") ?></p>
                <div class="row">
                    <div class="col"># <?= _("Your text") ?></div>
                    <div class="col"><?= _("First title") ?></div>
                </div>
                <div class="row">
                    <div class="col">## <?= _("Your text") ?></div>
                    <div class="col"><?= _("Second title") ?></div>
                </div>
                <div class="row">
                    <div class="col">### <?= _("Your text") ?></div>
                    <div class="col"><?= _("Third title") ?></div>
                </div>
                <div class="row">
                    <div class="col">**<?= _("Your text") ?>**</div>
                    <div class="col"><?= _("Bold") ?></div>
                </div>
                <div class="row">
                    <div class="col">_<?= _("Your text") ?>_</div>
                    <div class="col"><?= _("Italic") ?></div>
                </div>
                <div class="row">
                    <div class="col">&gt;<?= _("Your text") ?></div>
                    <div class="col"><?= _("Quote (each line must start with this symbol)") ?></div>
                </div>
                <div class="row">
                    <div class="col">[ ] <?= _("Your optionnal text") ?></div>
                    <div class="col"><?= _("Checkbox unchecked (there is a space between \"[\" and \"]\")") ?></div>
                </div>
                <div class="row">
                    <div class="col">[x] <?= _("Your optionnal text") ?></div>
                    <div class="col"><?= _("Checkbox checked") ?></div>
                </div>
                <div class="row">
                    <div class="col">![<?= _("Your optionnal text") ?>](<?= _("image url") ?>)</div>
                    <div class="col"><?= _("Image with description") ?></div>
                </div>
                <h3 class="mt-3"><?= _("Tips") ?></h3>
                <ul>
                    <li><?= _("Click on a note's header to edit it.") ?></li>
                    <li><?= _("Click on an image to see it in a larger size.") ?></li>
                    <li><?= _("When you check a box, the corresponding markdown is immediately modified and saved.") ?></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
            </div>
        </div>
    </div>
</div>