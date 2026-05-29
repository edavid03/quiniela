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
