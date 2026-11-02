import {openModal} from './modal';

export function initBinderLightbox(modal) {
    const photos = [...document.querySelectorAll("[data-binder-photo]")];
    const image = modal.querySelector("[data-binder-lightbox-image]");
    const title = modal.querySelector("[data-binder-lightbox-title]");
    const sub = modal.querySelector("[data-binder-lightbox-sub]");
    let index = 0;

    function show(next) {
        index = (next + photos.length) % photos.length;
        const photo = photos[index];
        image.src = photo.dataset.binderPhoto;
        image.alt = photo.dataset.title;
        title.textContent = photo.dataset.title;
        sub.textContent = `Taken ${photo.dataset.takenOn} · ${index + 1} of ${photos.length}`;
    }

    photos.forEach((photo, i) => photo.addEventListener("click", () => {
        show(i);
        openModal(modal);
    }));
    modal.querySelectorAll("[data-binder-lightbox-step]").forEach(button => {
        button.addEventListener("click", () => show(index + Number(button.dataset.binderLightboxStep)));
    });
    modal.addEventListener("modal:closed", () => image.removeAttribute("src"));
    document.addEventListener("keydown", e => {
        if (!modal.classList.contains("open")) return;
        if (e.key === "ArrowLeft") show(index - 1);
        if (e.key === "ArrowRight") show(index + 1);
    });
}
