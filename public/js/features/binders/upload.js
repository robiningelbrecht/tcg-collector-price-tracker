import {dispatchCommand} from '../../components/dispatch-command-form';

const MAX_PHOTO_SIZE = 20 * 1024 * 1024;

const readAsDataUrl = file => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(reader.error);
    reader.readAsDataURL(file);
});

export function initBinderUpload(root) {
    const buttons = root.querySelectorAll("[data-binder-upload]");
    if (buttons.length === 0) return;

    const input = Object.assign(document.createElement("input"), {type: "file", accept: "image/*", hidden: true});
    input.setAttribute("capture", "environment");
    document.body.append(input);
    let target = null;

    buttons.forEach(button => button.addEventListener("click", () => {
        target = button;
        input.value = "";
        input.click();
    }));

    input.addEventListener("change", async () => {
        const file = input.files[0];
        if (!file || !target) return;
        if (file.size > MAX_PHOTO_SIZE) {
            window.alert("The photo is too large. The maximum is 20 MB.");
            return;
        }

        const tile = target.closest(".slot");
        tile.classList.add("uploading");

        try {
            await dispatchCommand("upload-binder-page-photo", {
                setId: target.dataset.binderUpload,
                pageNumber: target.dataset.pageNumber,
                takenOn: String(file.lastModified),
                photo: await readAsDataUrl(file),
            });
            window.location.reload();
        } catch (error) {
            tile.classList.remove("uploading");
            window.alert(error.message);
        }
    });
}
