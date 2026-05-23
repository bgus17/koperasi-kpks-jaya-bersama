import { renderLogin } from './modules/auth.js';
import { renderDashboard } from './modules/dashboard.js';
import { renderFormPage } from './modules/form-page.js';
import { ensureFreshSession } from './modules/shell.js';
import { isAuthenticated } from './core/session.js';
import { getRoute, navigate, onRouteChange, startRouter } from './core/router.js';

const root = document.getElementById('app');

onRouteChange(async (route) => {
    if (route === 'login') {
        renderLogin(root);
        return;
    }

    if (!isAuthenticated()) {
        navigate('login');
        return;
    }

    try {
        await ensureFreshSession();
    } catch {
        navigate('login');
        return;
    }

    if (route.startsWith('form/')) {
        const slug = route.replace(/^form\//, '');
        await renderFormPage(root, slug);
        return;
    }

    renderDashboard(root);
});

if (!window.location.hash) {
    navigate(isAuthenticated() ? 'dashboard' : 'login');
} else if (getRoute() !== 'login' && !isAuthenticated()) {
    navigate('login');
}

startRouter();
