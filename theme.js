async function themeLoadAndApply() {

    // Load the theme
    let theme = localStorage.getItem("theme");
    if (!theme) theme = "dark";

    // Apply the theme
    document.documentElement.setAttribute("data-bs-theme", theme);

    // Save the theme server side
    try {
        await postData("theme.ajax.php", { theme }, "text");
    } catch (err) {
        console.error("Erreur lors de la sauvegarde du thème :", err);
    }
}

function themeSave(theme) {

    // Save the theme
    localStorage.setItem("theme", theme);

}

function themeToggle() {

    // Switch theme
    const currentTheme = document.documentElement.getAttribute("data-bs-theme") || "dark";
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    themeSave(newTheme);
    themeLoadAndApply();
}

// Load and apply the theme
themeLoadAndApply();
