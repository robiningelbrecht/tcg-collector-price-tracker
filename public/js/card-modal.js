import {destroyCharts, initCharts} from './charts';
import {openModal} from './modal';

export function initCardModal(modal) {
    const body = modal.querySelector("[data-card-modal-body]");
    let request = 0;

    async function open(url) {
        const current = ++request;
        if (!modal.classList.contains("open")) {
            body.innerHTML = '<div class="empty p-5">Loading…</div>';
            openModal(modal);
        }
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(response.statusText);
            const html = await response.text();
            if (current !== request) return;
            destroyCharts(body);
            body.innerHTML = html;
            initCharts(body);
        } catch {
            if (current === request) body.innerHTML = '<div class="empty p-5">Could not load this card.</div>';
        }
    }

    modal.addEventListener("modal:closed", () => {
        request++;
        destroyCharts(body);
        body.innerHTML = "";
    });
    document.addEventListener("click", e => {
        const trigger = e.target.closest("[data-card-url]");
        if (trigger) open(trigger.dataset.cardUrl);
    });
}
