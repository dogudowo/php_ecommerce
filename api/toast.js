function showToast(message, type = "success") {
    const toastContainer = document.querySelector(
        ".position-fixed.bottom-0.end-0.p-3"
    );
    if (!toastContainer) {
        console.error("Toast container not found");
        return;
    }

    const toastEl = document.createElement("div");
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute("role", "alert");
    toastEl.setAttribute("aria-live", "assertive");
    toastEl.setAttribute("aria-atomic", "true");
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    // 移除 Toast 元素以避免 DOM 累積
    toastEl.addEventListener("hidden.bs.toast", () => {
        toastEl.remove();
    });
}
