import './bootstrap';

const applyTheme = (theme) => {
    const resolvedTheme = theme === 'dark' ? 'dark' : 'light';

    document.documentElement.classList.toggle('dark', resolvedTheme === 'dark');
    document.documentElement.dataset.theme = resolvedTheme;
    localStorage.setItem('theme', resolvedTheme);
};

const storedTheme = localStorage.getItem('theme');
const preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

applyTheme(storedTheme ?? preferredTheme);

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');

    if (! button) {
        return;
    }

    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    applyTheme(nextTheme);
});

// Ojito de los campos de contrasena: alterna entre password/text y los iconos.
// Delegado en document para que aplique a cualquier <x-password-input> sin init por pagina.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (! toggle) {
        return;
    }

    const field = toggle.closest('[data-password-field]');
    const input = field?.querySelector('[data-password-input]');

    if (! input) {
        return;
    }

    const willShow = input.type === 'password';
    input.type = willShow ? 'text' : 'password';
    toggle.setAttribute('aria-label', willShow ? 'Ocultar contrasena' : 'Mostrar contrasena');
    toggle.querySelector('[data-eye-show]')?.classList.toggle('hidden', willShow);
    toggle.querySelector('[data-eye-hide]')?.classList.toggle('hidden', ! willShow);
});

const renderLocalTimes = () => {
    const formatter = new Intl.DateTimeFormat(navigator.language || 'es', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZoneName: 'short',
    });

    document.querySelectorAll('[data-local-time]').forEach((element) => {
        const date = new Date(element.dataset.localTime);

        if (Number.isNaN(date.getTime())) {
            return;
        }

        element.textContent = formatter.format(date);
    });
};

renderLocalTimes();

// Evita el doble-submit: al loguearse o activar la cuenta el token CSRF rota,
// y un segundo envio (doble toque, o el reintento de un webview in-app) llega
// con el token viejo -> 419. Bloqueamos el reenvio y deshabilitamos el boton.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement)) {
        return;
    }

    if (form.dataset.submitting === 'true') {
        event.preventDefault();

        return;
    }

    form.dataset.submitting = 'true';

    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

    if (submitButton) {
        // Diferido para no cancelar el envio en curso.
        window.setTimeout(() => {
            submitButton.disabled = true;
        }, 0);
    }
});

// bfcache: al volver atras con el boton del navegador, reactivar los formularios.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
        delete form.dataset.submitting;

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

        if (submitButton) {
            submitButton.disabled = false;
        }
    });
});

// Formulario de contacto via fetch: envia sin recargar la pagina y muestra el
// resultado inline. Progressive enhancement: sin JS el form hace el POST clasico.
const setupContactForm = () => {
    const form = document.querySelector('form[data-contact-form]');

    if (! form) {
        return;
    }

    const feedback = form.querySelector('[data-contact-feedback]');
    const submitButton = form.querySelector('button[type="submit"]');
    const defaultLabel = submitButton?.textContent;

    // Mismas clases que los bloques server-rendered del blade (Tailwind ya las genera).
    const okClass = 'mb-1 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30';
    const errClass = 'alert mb-1 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30';

    const showFeedback = (message, ok) => {
        if (! feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.className = ok ? okClass : errClass;
    };

    const reset = () => {
        delete form.dataset.submitting;

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = defaultLabel;
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (submitButton) {
            submitButton.textContent = 'Enviando…';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                showFeedback(data.status ?? 'Mensaje enviado, te contactamos pronto.', true);
                form.reset();
            } else if (response.status === 422) {
                const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : data.message;
                showFeedback(firstError ?? 'Revisa los datos e intenta de nuevo.', false);
            } else if (response.status === 429) {
                showFeedback('Demasiados intentos. Espera un momento y volve a probar.', false);
            } else {
                showFeedback('No pudimos enviar tu mensaje. Intenta de nuevo en unos minutos.', false);
            }
        } catch {
            showFeedback('No pudimos enviar tu mensaje. Revisa tu conexion e intenta de nuevo.', false);
        } finally {
            reset();
        }
    });
};

setupContactForm();
