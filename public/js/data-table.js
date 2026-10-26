const datasetKey = (prefix, name) => prefix + name.charAt(0).toUpperCase() + name.slice(1);

function initDataTable(wrapper) {
    const tbody = wrapper.querySelector("tbody");
    const rows = [...tbody.rows];
    const search = wrapper.querySelector("[data-table-search]");
    const filters = [...wrapper.querySelectorAll("[data-table-filter]")];
    const count = wrapper.querySelector("[data-table-count]");
    const headers = [...wrapper.querySelectorAll("th[data-sort]")];

    function applyFilters() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const show = (!query || row.dataset.search.includes(query))
                && filters.every(filter => !filter.value || row.dataset[datasetKey("filter", filter.dataset.tableFilter)] === filter.value);
            row.hidden = !show;
            if (show) visible++;
        });
        count.textContent = `${visible} of ${rows.length}`;
    }

    function sortOn(header, ascending) {
        const key = datasetKey("sort", header.dataset.sort);
        const isString = header.dataset.sortType === "string";
        rows.sort((a, b) => {
            const x = a.dataset[key], y = b.dataset[key];
            return (isString ? x.localeCompare(y) : x - y) * (ascending ? 1 : -1);
        });
        tbody.append(...rows);
        headers.forEach(h => h === header ? h.setAttribute("aria-sort", ascending ? "ascending" : "descending") : h.removeAttribute("aria-sort"));
    }

    headers.forEach(header => header.addEventListener("click", () => {
        const current = header.getAttribute("aria-sort");
        const defaultAscending = header.dataset.sortType === "string" || header.dataset.sort === "rank";
        sortOn(header, current ? current !== "ascending" : defaultAscending);
    }));
    search.addEventListener("input", applyFilters);
    filters.forEach(filter => filter.addEventListener("input", applyFilters));
}

export function initDataTables(root) {
    root.querySelectorAll("[data-table]").forEach(initDataTable);
}
