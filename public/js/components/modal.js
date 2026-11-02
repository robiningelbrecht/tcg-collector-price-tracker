export function openModal(modal) {
    modal.classList.add("open");
    document.body.style.overflow = "hidden";
    modal.querySelector("[autofocus]")?.focus();
}

export function closeModal(modal) {
    if (!modal.classList.contains("open")) return;
    modal.classList.remove("open");
    document.body.style.overflow = "";
    modal.dispatchEvent(new CustomEvent("modal:closed"));
}

export function initModals(root) {
    root.querySelectorAll("[data-modal-open]").forEach(button => {
        const modal = document.querySelector(`[data-modal="${button.dataset.modalOpen}"]`);
        if (modal) button.addEventListener("click", () => openModal(modal));
    });
    root.querySelectorAll("[data-modal]").forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal || e.target.closest("[data-modal-close]")) closeModal(modal);
        });
    });
    document.addEventListener("keydown", e => {
        if (e.key === "Escape") root.querySelectorAll("[data-modal].open").forEach(closeModal);
    });
}
