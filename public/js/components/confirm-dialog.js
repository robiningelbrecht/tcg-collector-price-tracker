import {closeModal, openModal} from './modal';

export function confirmDialog(message, label) {
    const modal = document.querySelector('[data-confirm-modal]');
    if (!modal) {
        return Promise.resolve(window.confirm(message));
    }

    modal.querySelector('[data-confirm-message]').textContent = message;
    const accept = modal.querySelector('[data-confirm-accept]');
    accept.textContent = label;

    return new Promise((resolve) => {
        let confirmed = false;
        const onAccept = () => {
            confirmed = true;
            closeModal(modal);
        };
        accept.addEventListener('click', onAccept);
        modal.addEventListener('modal:closed', () => {
            accept.removeEventListener('click', onAccept);
            resolve(confirmed);
        }, {once: true});
        openModal(modal);
    });
}
