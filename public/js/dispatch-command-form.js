const dispatchCommand = async (commandName, payload) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        throw new Error('csrf-token expected to be found');
    }
    const url = document.querySelector('meta[name="dispatch-command-url"]')?.getAttribute('content');
    if (!url) {
        throw new Error('dispatch-command-url expected to be found');
    }

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({commandName, payload}),
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.error ?? `Failed to dispatch ${commandName}: ${response.status}`);
    }
};

const showError = (box, message) => {
    if (!box) {
        window.alert(message);
        return;
    }
    box.textContent = message;
    box.classList.remove('hidden');
};

const hideError = (box) => {
    if (!box) return;
    box.textContent = '';
    box.classList.add('hidden');
};

const setSubmitting = (form, submitting) => {
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = submitting;
    });
};

export function initDispatchCommandForms(rootNode = document) {
    rootNode.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-dispatch-command]')) {
            return;
        }

        event.preventDefault();

        const confirmMessage = event.submitter?.dataset.confirm;
        if (confirmMessage && !window.confirm(confirmMessage)) {
            return;
        }

        const errorBox = form.querySelector('[data-form-error]');
        hideError(errorBox);
        setSubmitting(form, true);

        const payload = {};
        new FormData(form, event.submitter).forEach((value, key) => {
            payload[key] = value;
        });
        const commandName = event.submitter?.dataset.dispatchCommand ?? form.dataset.dispatchCommand;

        try {
            await dispatchCommand(commandName, payload);
            window.location.assign(form.dataset.redirect ?? window.location.href);
        } catch (error) {
            showError(errorBox, error.message);
            setSubmitting(form, false);
        }
    });
}
