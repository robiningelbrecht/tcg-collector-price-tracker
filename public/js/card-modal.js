import {initCardDetail} from './card-detail';

export function initCardModal(modal) {
    const body = modal.querySelector("[data-card-modal-body]");
    let destroyCardDetail = null, request = 0;

    async function open(url) {
        const current = ++request;
        destroyCardDetail?.();
        destroyCardDetail = null;
        body.innerHTML = '<div class="empty p-5">Loading…</div>';
        modal.classList.add("open");
        document.body.style.overflow = "hidden";
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(response.statusText);
            const html = await response.text();
            if (current !== request) return;
            body.innerHTML = html;
            destroyCardDetail = initCardDetail(body);
        } catch {
            if (current === request) body.innerHTML = '<div class="empty p-5">Could not load this card.</div>';
        }
    }

    function close() {
        request++;
        destroyCardDetail?.();
        destroyCardDetail = null;
        modal.classList.remove("open");
        body.innerHTML = "";
        document.body.style.overflow = "";
    }

    document.addEventListener("click", e => {
        const trigger = e.target.closest("[data-card-url]");
        if (trigger) open(trigger.dataset.cardUrl);
    });
    modal.querySelector("[data-card-modal-close]").addEventListener("click", close);
    modal.addEventListener("click", e => { if (e.target === modal) close(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape") close(); });
}
