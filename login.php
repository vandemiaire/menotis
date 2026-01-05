<?php
include("toasts.inc.php");
?>

<body>

    <div class="card mt-5 mx-auto" style="width:250px;">
        <div class="card-header"><?= sprintf(_("Log in to %s"), APP_NAME) ?></div>
        <div class="card-body">
            <form>
                <div class="mb-3">
                    <label for="login" class="form-label"><?= _("Login") ?></label>
                    <input type="text" class="form-control" id="login">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><?= _("Password") ?></label>
                    <input type="password" class="form-control" id="password">
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <button id="login-button" type="submit" class="btn btn-primary"><?= _("Submit") ?></button>
        </div>
    </div>

</body>

<script>
    window.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".card-body form");
        const loginButton = document.getElementById("login-button");

        if (!form || !loginButton) return;

        // Focus sur le premier champ du formulaire
        const firstInput = form.querySelector("input");
        if (firstInput) firstInput.focus();

        // Soumettre le formulaire en appuyant sur Entrée
        form.querySelectorAll("input").forEach(input => {
            input.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    loginButton.click();
                }
            });
        });
    });

    document.getElementById("login-button").addEventListener("click", async () => {
        const login = document.getElementById("login").value;
        const password = document.getElementById("password").value;

        try {
            const data = await postData("security.ajax.php", {
                action: "login",
                login,
                password
            }, "json");

            if (data.auth === true) {
                window.location.reload();
            } else {
                toastError(data.message);
            }

        } catch (err) {
            console.error("Erreur lors de la connexion :", err);
            toastError("Erreur lors de la connexion.");
        }
    });
</script>