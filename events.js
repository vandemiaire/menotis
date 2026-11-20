// ----------------------
// Checkboxes [ ] and [x]
// ----------------------
document.addEventListener('change', e => {
    if (!e.target.classList.contains('md-checkbox')) return;

    // Send the infos to save the new markdown
    postData("checkbox.ajax.php", {
        action: "check",
        index: e.target.dataset.index,
        uid: e.target.dataset.uid,
        checked: e.target.checked ? 1 : 0
    }, "json");

});

// ----------------------
// Switch theme button
// ----------------------
document.getElementById("link-theme").addEventListener("click", () => {
    themeToggle(); // Call the function that toggles dark/light theme
});

// ----------------------
// Click on note card header
// ----------------------
document.querySelectorAll(".card-header").forEach(header => {
    header.addEventListener("click", () => {
        const uid = header.dataset.uid;
        noteEdit(uid); // Open edit modal for this note
    });
});

// ----------------------
// Settings modal
// ----------------------
document.getElementById("link-modal-settings").addEventListener("click", async () => {

    // Initialize the modal
    const modalSettings = new bootstrap.Modal(
        document.getElementById("modal-settings"),
        { keyboard: true }
    );

    // Send 3 requests in parallel to get login, timezones, and locales
    const [loginData, timezonesData, localesData, timeFormats] = await Promise.all([
        postData("settings.ajax.php", { action: "getlogin" }, "text"),
        postData("settings.ajax.php", { action: "gettimezones", default: "" }, "text"),
        postData("settings.ajax.php", { action: "getlocales", default: "" }, "text"),
        postData("settings.ajax.php", { action: "gettimeformats" }, "text")
    ]);

    // Fill input fields with fetched data
    const loginInput = document.getElementById("login-settings");
    if (loginInput) loginInput.value = loginData;

    const timezoneSelect = document.getElementById("timezone-select-settings");
    if (timezoneSelect) timezoneSelect.innerHTML = timezonesData;

    const languageSelect = document.getElementById("language-select-settings");
    if (languageSelect) languageSelect.innerHTML = localesData;

    const timeFormatsSelect = document.getElementById("time-format-select-settings");
    if (timeFormats) timeFormatsSelect.innerHTML = timeFormats;

    // Show the modal
    modalSettings.show();
});

// ----------------------
// Button "Add note"
// ----------------------
const addNoteBtn = document.getElementById("link-add-note");
if (addNoteBtn) {
    addNoteBtn.addEventListener("click", () => {
        // Clear UID and fields
        document.getElementById("currentuid").value = "";
        document.getElementById("edittitle").value = "";
        document.getElementById("editcontent").value = "";

        // Initialize and show edit modal
        const modalEdit = new bootstrap.Modal(
            document.getElementById("modal-edit"),
            { keyboard: true }
        );
        modalEdit.show();

        // Focus on the title input
        const title = document.getElementById("edittitle");
        title.focus();
    });
}

// ----------------------
// Button "About"
// ----------------------
document.getElementById("link-about").addEventListener("click", () => {
    const modalAbout = new bootstrap.Modal(
        document.getElementById("modal-about"),
        { keyboard: true }
    );
    modalAbout.show();
});

// ----------------------
// Button "Logout"
// ----------------------
document.getElementById("link-logout").addEventListener("click", async () => {
    await postData("security.ajax.php", { action: "logout" }); // Send logout request
    window.location.reload(); // Reload page after logout
});

// ----------------------
// Buttons "Pin/Unpin" notes
// ----------------------
document.querySelectorAll(".note-btn-pin").forEach(button => {
    button.addEventListener("click", async () => {
        const uid = button.dataset.uid;
        await postData("notes.ajax.php", { action: "pin", uid }); // Pin or unpin note
        window.location.reload(); // Reload to reflect change
    });
});

// ----------------------
// Checkboxes
// ----------------------
document.querySelectorAll(".note-btn-checkboxes-add").forEach(button => {
    button.addEventListener("click", async () => {
        const uid = button.dataset.uid;
        await postData("checkbox.ajax.php", { action: "add", uid });
        noteRefresh(uid);
    });
});
document.querySelectorAll(".note-btn-checkboxes-remove").forEach(button => {
    button.addEventListener("click", async () => {
        const uid = button.dataset.uid;
        await postData("checkbox.ajax.php", { action: "remove", uid });
        noteRefresh(uid);
    });
});

// ----------------------
// Buttons "Archive/Unarchive" notes
// ----------------------
document.querySelectorAll(".note-btn-archive").forEach(button => {
    button.addEventListener("click", async () => {
        const uid = button.dataset.uid;
        await postData("notes.ajax.php", { action: "archive", uid }); // Archive/unarchive note
        window.location.reload();
    });
});

// ----------------------
// Button to show all images
// ----------------------
document.getElementById("link-modal-images").addEventListener("click", () => {
    imagesListRefresh(); // Load images in the modal

    const modalImages = new bootstrap.Modal(
        document.getElementById("modal-images"),
        { keyboard: true }
    );
    modalImages.show(); // Show the modal
});

// ----------------------
// Backup modal button
// ----------------------
document.getElementById("link-modal-backup").addEventListener("click", () => {
    const modalBackup = new bootstrap.Modal(
        document.getElementById("modal-backup"),
        { keyboard: true }
    );
    modalBackup.show();
});

// Delete button
document.querySelectorAll(".note-btn-modal-delete").forEach(button => {
    button.addEventListener("click", function () {

        // Get data attributes
        let uid = this.dataset.uid;
        document.getElementById("currentuid").value = uid;

        // Show delete modal
        const modalDelete = new bootstrap.Modal(
            document.getElementById("modal-delete"),
            { keyboard: true }
        );
        modalDelete.show();

    });
});

// ----------------------
// Note color button
// ----------------------
document.querySelectorAll(".note-btn-modal-color").forEach(button => {
    button.addEventListener("click", function () {

        // Get data attributes
        let uid = this.dataset.uid;
        let bgcolor = this.dataset.bgcolor || "#111111"; // default color
        let bgimage = this.dataset.bgimage;

        document.getElementById("currentuid").value = uid;
        document.getElementById("modal-color-value").value = bgcolor;
        document.getElementById("modal-background-value").value = bgimage;

        // Load palette content
        fetch("palettes.ajax.php", { method: "POST", headers: MENOTIS_HEADERS })
            .then(res => res.text())
            .then(data => {
                document.getElementById("modal-color-palettes").innerHTML = data;
            });

        // Show color modal
        const modalColor = new bootstrap.Modal(
            document.getElementById("modal-color"),
            { keyboard: true }
        );
        modalColor.show();
    });
});

// ----------------------
// Delete note
// ----------------------
document.getElementById("modal-delete-yes").addEventListener("click", async () => {
    const uid = document.getElementById("currentuid").value;
    await postData("notes.ajax.php", { action: "delete", uid });

    const card = document.getElementById("card-" + uid);
    if (card) card.remove();

});

// ----------------------
// Upload image button
// ----------------------
document.querySelectorAll(".note-btn-modal-upload-image").forEach(button => {
    button.addEventListener("click", () => {
        const uid = button.dataset.uid;

        // Clear upload inputs
        document.getElementById("upload-title").value = "";
        document.getElementById("upload-file-image").value = "";

        // Save UID
        document.getElementById("currentuid").value = uid;

        // Show upload modal
        const modalUpload = new bootstrap.Modal(
            document.getElementById("modal-upload-image"),
            { keyboard: true }
        );
        modalUpload.show();
    });
});

// ----------------------
// Edit note button
// ----------------------
document.querySelectorAll(".note-btn-modal-edit").forEach(button => {
    button.addEventListener("click", () => {
        const uid = button.dataset.uid;
        noteEdit(uid); // Open edit modal for this note
    });
});

// ----------------------
// Search box functionality
// ----------------------
const searchBox = document.getElementById("search-box");
searchBox.addEventListener("input", () => {
    const inputVal = searchBox.value.toUpperCase();
    const searchItems = document.querySelectorAll(".search-here");

    searchItems.forEach(item => {
        const card = document.getElementById("card-" + item.dataset.uid);

        if (inputVal.length < 2) {
            card.style.display = ""; // Show all if less than 2 characters
            return;
        }

        // Show only if item text matches input
        card.style.display = item.textContent.toUpperCase().includes(inputVal) ? "" : "none";
    });

});

// ----------------------
// Initialize Bootstrap tooltips
// ----------------------
const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
const tooltipList = tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
