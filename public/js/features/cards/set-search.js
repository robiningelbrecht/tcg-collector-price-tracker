export function initSetSearch(root) {
    const input = root.querySelector('[data-set-search-input]');
    const results = root.querySelector('[data-set-search-results]');
    const empty = root.querySelector('[data-set-search-empty]');
    const items = [...root.querySelectorAll('[data-set-search-item]')];

    const visibleItems = () => items.filter((item) => !item.hidden);

    const open = () => results.classList.remove('hidden');
    const close = () => results.classList.add('hidden');

    const filter = () => {
        const words = input.value.toLowerCase().split(/\s+/).filter((word) => '' !== word);
        items.forEach((item) => {
            const haystack = item.dataset.search;
            item.hidden = !words.every((word) => haystack.includes(word));
        });
        empty.classList.toggle('hidden', visibleItems().length > 0);
    };

    input.addEventListener('focus', () => {
        input.select();
        items.forEach((item) => {
            item.hidden = false;
        });
        empty.classList.add('hidden');
        open();
    });

    input.addEventListener('input', () => {
        filter();
        open();
    });

    root.addEventListener('keydown', (event) => {
        const visible = visibleItems();
        const index = visible.indexOf(document.activeElement);

        if ('Escape' === event.key) {
            close();
            input.blur();
        } else if ('ArrowDown' === event.key) {
            event.preventDefault();
            open();
            visible[Math.min(index + 1, visible.length - 1)]?.focus();
        } else if ('ArrowUp' === event.key) {
            event.preventDefault();
            if (index <= 0) {
                input.focus();
            } else {
                visible[index - 1].focus();
            }
        } else if ('Enter' === event.key && document.activeElement === input) {
            event.preventDefault();
            if (visible[0]) {
                window.location.assign(visible[0].href);
            }
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            close();
        }
    });
}
