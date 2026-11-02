export function initTabs(root) {
    root.querySelectorAll("[data-tabs] [data-tab]").forEach(button => {
        button.addEventListener("click", () => {
            const group = button.closest("[data-tabs]").dataset.tabs;
            const value = button.dataset.tab;
            document.querySelectorAll(`[data-tabs="${group}"] [data-tab]`).forEach(tab => {
                const active = tab.dataset.tab === value;
                tab.classList.toggle("on", active);
                tab.setAttribute("aria-selected", active);
            });
            document.querySelectorAll(`[data-tab-pane="${group}"]`).forEach(pane => {
                pane.hidden = pane.dataset.tab !== value;
            });
        });
    });
}
