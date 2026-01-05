/**
 * Sends a POST request and returns the response.
 * @param {string} url - The URL to send the request to.
 * @param {Object} params - Key/value pairs to send as POST parameters.
 * @param {string} responseType - Expected response type: "json" or "text" (default is "json").
 * @returns {Promise<any>} - The parsed response, either JSON or plain text.
 */
async function postData(url, params = {}, responseType = "json") {
    try {
        // Send a POST request using fetch
        const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams(params) // Convert params object to URL-encoded string
        });

        // Parse and return the response based on the requested type
        return responseType === "json" ? await response.json() : await response.text();

    } catch (err) {
        // Log any errors and rethrow them for the caller to handle
        console.error(`Error POSTing to ${url}:`, err);
        throw err;
    }
}

/**
 * Assigns the selected color from a palette to the color input in the modal.
 * @param {HTMLElement} element - The clicked palette element.
 * @returns {boolean} Always returns false to prevent default action.
 */
function paletteSelect(element) {
    document.getElementById("modal-color-value").value = element.dataset.bgcolor;
    return false;
}

/**
 * Displays a clicked image in a modal for a larger view.
 * @param {HTMLElement} element - The clicked image element.
 * @returns {boolean} Always returns false to prevent default action.
 */
function imageView(element) {
    // Set the source of the modal image
    document.getElementById("image-single").src = element.src;

    // Initialize and show the Bootstrap modal
    const modal = new bootstrap.Modal(
        document.getElementById("modal-image-single"),
        { keyboard: true }
    );
    modal.show();
    return false;
}

/**
 * Opens the edit note modal and loads the note content and title.
 * @param {string} uid - The unique identifier of the note.
 */
async function noteEdit(uid) {
    // Save the current note UID
    document.getElementById("currentuid").value = uid;

    // Initialize the edit modal
    const modalEdit = new bootstrap.Modal(
        document.getElementById("modal-edit"),
        { keyboard: true }
    );

    // Clear fields initially
    document.getElementById("edittitle").value = "";
    document.getElementById("editcontent").value = "";

    // Fetch note content and set it
    const contentData = await postData("notes.ajax.php", { action: "getcontent", uid }, "text");
    document.getElementById("editcontent").value = contentData;

    // Fetch note title and set it
    const titleData = await postData("notes.ajax.php", { action: "gettitle", uid }, "text");
    document.getElementById("edittitle").value = titleData;

    // Show the edit modal
    modalEdit.show();

    // Focus on the title input
    const title = document.getElementById("edittitle");
    title.focus();
}

/**
 * Refreshes a note’s content and metadata on the page.
 * @param {string} uid - The unique identifier of the note.
 */
async function noteRefresh(uid) {
    // Get the note data via AJAX
    const data = await postData("notes.ajax.php", { action: "infos", uid }, "json");

    // Update title and modified date
    document.getElementById("title-" + uid).innerHTML = data.title;
    document.getElementById("modified-" + uid).innerHTML = data.modified;

    // Update note content
    const contentContainer = document.getElementById("content-" + uid);
    contentContainer.innerHTML = data.content;

    // Wait for all images to load before refreshing the layout
    await waitForImages(contentContainer);

}

/**
 * Waits for all images within a container to finish loading.
 * @param {HTMLElement} container - The container element to check.
 * @returns {Promise} Resolves when all images are loaded or errored.
 */
function waitForImages(container) {
    const images = container.querySelectorAll("img");

    const promises = Array.from(images).map(img => {
        return new Promise(resolve => {
            if (img.complete && img.naturalWidth !== 0) {
                resolve(); // déjà chargé
            } else {
                img.addEventListener("load", resolve, { once: true });
                img.addEventListener("error", resolve, { once: true });
            }
        });
    });

    return Promise.all(promises);
}

/**
 * Refreshes the list of images displayed in the modal.
 */
async function imagesListRefresh() {
    const data = await postData("modalimages.ajax.php", {}, "text");
    document.getElementById("modal-images-list").innerHTML = data;
}

/**
 * Deletes an image and refreshes the images list.
 * @param {HTMLElement} element - The clicked delete button element with a data-basename attribute.
 * @returns {boolean} Always returns false to prevent default action.
 */
async function imageDelete(element) {
    const basename = element.dataset.basename;

    // Send request to delete the image
    await postData("images.ajax.php", { action: "delete", basename });

    // Refresh the modal image list
    await imagesListRefresh();

    return false;
}
