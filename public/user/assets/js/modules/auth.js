import { apiRequest, ApiError } from '../core/http.js';
import { qs, escapeHtml, setDocumentTitle } from '../core/dom.js';
import { navigate } from '../core/router.js';
import { setSession } from '../core/session.js';
import { securityContext } from '../core/security.js';

export function renderLogin(root) {
    setDocumentTitle('Login');
    const security = securityContext();

    root.innerHTML = `
        <section class="auth-page">
            <div class="auth-panel">
                <div>
                    <div class="brand-mark">KC</div>
                    <p class="eyebrow">Portal Aktor</p>
                    <h1 class="title">Masuk ke sistem lapangan</h1>
                    <p class="subtitle">Gunakan akun Mandor atau Staff/Operator untuk mengirim data transaksi lewat API.</p>
                </div>
                ${security.warning ? `<div class="notice error">${escapeHtml(security.warning)}</div>` : ''}
                <form class="auth-card" data-login-form>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" autocomplete="username" required placeholder="mandor@koperasi.com">
                    </div>
                    <div class="field" style="margin-top:16px;">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Password akun">
                    </div>
                    <div data-login-message style="margin-top:16px;"></div>
                    <button class="btn btn-primary" type="submit" style="width:100%;margin-top:18px;">Masuk</button>
                </form>
            </div>
            <div class="auth-visual">
                <div>
                    <p class="eyebrow" style="color:rgba(255,255,255,.64);">Koperasi Cahaya Mulya</p>
                    <h2 class="title">Input data biaya kebun lebih tertib, cepat, dan terlacak.</h2>
                    <p class="subtitle" style="color:rgba(255,255,255,.72);">Akses menu mengikuti aktor pada use case, sementara validasi tetap dijaga di server.</p>
                </div>
            </div>
        </section>
    `;

    qs('[data-login-form]', root).addEventListener('submit', (event) => handleLogin(event, root));
}

async function handleLogin(event, root) {
    event.preventDefault();

    const form = event.currentTarget;
    const message = qs('[data-login-message]', root);
    const submit = qs('button[type="submit"]', form);
    const formData = new FormData(form);

    message.innerHTML = '';
    submit.disabled = true;
    submit.textContent = 'Memproses...';

    try {
        const response = await apiRequest('/auth/login', {
            method: 'POST',
            body: {
                email: formData.get('email'),
                password: formData.get('password'),
                device_name: 'portal-user-public',
            },
        });

        setSession({
            token: response.token,
            actor: response.actor,
            menus: response.menus,
        });

        navigate('dashboard');
    } catch (error) {
        message.innerHTML = `<div class="notice error">${escapeHtml(errorMessage(error))}</div>`;
    } finally {
        submit.disabled = false;
        submit.textContent = 'Masuk';
    }
}

function errorMessage(error) {
    if (error instanceof ApiError && error.payload?.errors) {
        return Object.values(error.payload.errors).flat().join(' ');
    }

    return error.message || 'Login gagal.';
}
