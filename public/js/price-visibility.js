export function initPriceToggle(root) {
    const html = document.documentElement;
    root.querySelectorAll("[data-price-toggle]").forEach(button => {
        button.setAttribute("aria-pressed", html.classList.contains("prices-hidden"));
        button.addEventListener("click", () => {
            const hidden = html.classList.toggle("prices-hidden");
            hidden ? localStorage.setItem("hidePrices", "1") : localStorage.removeItem("hidePrices");
            button.setAttribute("aria-pressed", hidden);
        });
    });
}
