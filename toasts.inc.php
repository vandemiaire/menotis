<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast-error" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div id="toast-error-text" class="toast-body text-light bg-danger">
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast-success" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div id="toast-success-text" class="toast-body text-light bg-success">
        </div>
    </div>
</div>

<script>
    function toastError(text) {

        document.getElementById("toast-error-text").textContent = text;

        // Récupérer le toast et l'afficher
        const toastErrorEl = document.getElementById("toast-error");
        const bsToastError = new bootstrap.Toast(toastErrorEl);
        bsToastError.show();

    }

    function toastSuccess(text) {

        // Mettre le texte dans le toast
        document.getElementById("toast-success-text").textContent = text;

        // Récupérer le toast et l'afficher
        const toastSuccessEl = document.getElementById("toast-success");
        const bsToastSuccess = new bootstrap.Toast(toastSuccessEl);
        bsToastSuccess.show();

    }
</script>