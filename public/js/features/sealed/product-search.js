export function initSealedProductSearch(modal) {
    const input = modal.querySelector("[data-sealed-search-input]");
    const results = modal.querySelector("[data-sealed-search-results]");
    let request = 0;
    let timer;

    async function search() {
        const current = ++request;
        const url = new URL(modal.dataset.sealedSearch, window.location.origin);
        url.searchParams.set("q", input.value.trim());
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(response.statusText);
            const html = await response.text();
            if (current === request) results.innerHTML = html;
        } catch {
            if (current === request) results.innerHTML = '<div class="empty">Could not search products.</div>';
        }
    }

    input.addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });
}
