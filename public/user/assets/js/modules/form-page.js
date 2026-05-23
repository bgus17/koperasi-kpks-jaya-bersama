import { apiRequest, ApiError } from '../core/http.js';
import { compactText, escapeHtml, qs, qsa, setDocumentTitle } from '../core/dom.js';
import { renderShell } from './shell.js';
import { bindRekap } from './rekap.js';

let currentForm = null;

export async function renderFormPage(root, slug) {
    setDocumentTitle('Form');
    renderShell(root, '<div class="notice">Memuat form...</div>', slug);

    try {
        currentForm = await apiRequest(`/aktor/forms/${encodeURIComponent(slug)}`);

        if (currentForm.form?.mode === 'read_only') {
            renderReadOnlyForm(root, currentForm);
            return;
        }

        renderExpenseForm(root, currentForm);
    } catch (error) {
        renderShell(root, `<div class="notice error">${escapeHtml(error.message)}</div>`, slug);
    }
}

function renderReadOnlyForm(root, payload) {
    const year = new Date().getFullYear();

    renderShell(root, `
        <div class="panel">
            <p class="eyebrow">${escapeHtml(payload.menu.type)}</p>
            <h1 class="title">${escapeHtml(payload.menu.label)}</h1>
            <p class="subtitle">Menu ini bersifat baca data. Gunakan tombol di bawah untuk memuat ringkasan laporan dari API.</p>
        </div>
        <div class="form-card">
            <div class="form-grid">
                <div class="field">
                    <label for="rekap-year">Tahun</label>
                    <input id="rekap-year" data-rekap-year type="number" min="2000" max="2100" value="${year}">
                </div>
                <div class="field" style="justify-content:end;">
                    <button class="btn btn-primary" type="button" data-load-rekap>Load Rekap</button>
                </div>
            </div>
            <div data-rekap-result style="margin-top:20px;"></div>
        </div>
    `, payload.menu.slug);

    bindRekap(root);
}

function renderExpenseForm(root, payload) {
    const firstSub = payload.sub_options?.[0] ?? null;
    const selectedProfile = firstSub?.jenis_detail ?? 'umum';

    renderShell(root, `
        <div class="panel">
            <p class="eyebrow">${escapeHtml(payload.category.nomor_kategori)} - ${escapeHtml(payload.category.nama_kategori)}</p>
            <h1 class="title">${escapeHtml(payload.menu.label)}</h1>
            <p class="subtitle">Pilih kegiatan, isi data transaksi, lalu kirim ke API. Kategori dan sub kegiatan dikunci oleh akses aktor.</p>
        </div>
        <div class="form-card">
            <h2 style="margin-top:0;">Kegiatan</h2>
            <div class="sub-list" data-sub-list>
                ${(payload.sub_options ?? []).map((sub) => subButton(sub, firstSub?.id)).join('')}
            </div>
        </div>
        <form class="form-card" data-expense-form>
            <div data-form-message></div>
            <div data-dynamic-form>
                ${formHtmlForProfile(payload, selectedProfile)}
            </div>
            <div class="btn-row" style="margin-top:20px;">
                <button class="btn btn-primary" type="submit">Kirim Transaksi</button>
                <button class="btn btn-outline" type="reset">Reset Form</button>
            </div>
        </form>
    `, payload.menu.slug);

    bindExpenseForm(root, payload, firstSub);
}

function subButton(sub, activeId) {
    return `
        <button class="sub-option ${sub.id === activeId ? 'is-active' : ''}" type="button" data-sub-id="${escapeHtml(sub.id)}">
            <strong>${escapeHtml(sub.nama_sub)}</strong><br>
            <span>${escapeHtml(sub.jenis_detail)}</span>
        </button>
    `;
}

function bindExpenseForm(root, payload, initialSub) {
    let selectedSub = initialSub;
    const form = qs('[data-expense-form]', root);
    const dynamicForm = qs('[data-dynamic-form]', root);

    qsa('[data-sub-id]', root).forEach((button) => {
        button.addEventListener('click', () => {
            selectedSub = payload.sub_options.find((sub) => String(sub.id) === button.dataset.subId);
            qsa('[data-sub-id]', root).forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
            dynamicForm.innerHTML = formHtmlForProfile(payload, selectedSub.jenis_detail);
            bindAutoTotal(root);
        });
    });

    bindAutoTotal(root);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitExpenseForm(root, form, selectedSub);
    });
}

function formHtmlForProfile(payload, profile) {
    const schema = payload.form.schemas_by_profile?.[profile] ?? payload.form.schemas_by_profile?.umum;
    const fields = schema?.fields ?? [];
    const workerFields = schema?.worker_fields ?? [];
    const workers = payload.workers ?? [];

    return `
        <input type="hidden" data-field="kategori_id" value="${escapeHtml(payload.category.id)}">
        <input type="hidden" data-field="sub_id" value="">
        <div class="toolbar">
            <div>
                <p class="eyebrow">${escapeHtml(schema?.profile ?? profile)}</p>
                <h2 style="margin:0;">${escapeHtml(schema?.title ?? 'Detail Operasional')}</h2>
            </div>
        </div>
        <div class="form-grid">
            ${fields.map(fieldHtml).join('')}
        </div>
        ${schema?.requires_workers ? workerSectionHtml(workers, workerFields) : ''}
    `;
}

function fieldHtml(field) {
    if (field.type === 'readonly') {
        return `
            <div class="field">
                <label>${escapeHtml(field.label)}</label>
                <input data-field="${escapeHtml(field.name)}" type="text" readonly value="">
            </div>
        `;
    }

    if (field.type === 'textarea') {
        return `
            <div class="field" style="grid-column:1/-1;">
                <label for="${escapeHtml(field.name)}">${escapeHtml(field.label)}${field.required ? ' *' : ''}</label>
                <textarea id="${escapeHtml(field.name)}" data-field="${escapeHtml(field.name)}" ${field.required ? 'required' : ''}></textarea>
            </div>
        `;
    }

    if (field.type === 'boolean') {
        return `
            <label class="field check-field">
                <input data-field="${escapeHtml(field.name)}" type="checkbox">
                <span>${escapeHtml(field.label)}</span>
            </label>
        `;
    }

    if (field.type === 'select') {
        const options = (field.options ?? []).map((option) => {
            const value = typeof option === 'string' ? option : option.value;
            const label = typeof option === 'string' ? option : option.label;
            const selected = value === field.default ? 'selected' : '';

            return `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
        }).join('');

        return `
            <div class="field">
                <label for="${escapeHtml(field.name)}">${escapeHtml(field.label)}${field.required ? ' *' : ''}</label>
                <select id="${escapeHtml(field.name)}" data-field="${escapeHtml(field.name)}" ${field.required ? 'required' : ''}>
                    <option value="">Pilih</option>
                    ${options}
                </select>
            </div>
        `;
    }

    const type = field.type === 'date' ? 'date' : field.type === 'integer' || field.type === 'decimal' ? 'number' : 'text';
    const step = field.type === 'decimal' ? 'step="0.01"' : field.type === 'integer' ? 'step="1"' : '';
    const placeholder = field.type === 'money' ? '0' : '';

    return `
        <div class="field">
            <label for="${escapeHtml(field.name)}">${escapeHtml(field.label)}${field.required ? ' *' : ''}</label>
            <input id="${escapeHtml(field.name)}"
                   data-field="${escapeHtml(field.name)}"
                   data-type="${escapeHtml(field.type)}"
                   type="${type}"
                   ${step}
                   ${field.required ? 'required' : ''}
                   placeholder="${placeholder}">
        </div>
    `;
}

function workerSectionHtml(workers, workerFields) {
    return `
        <div class="panel" style="box-shadow:none;margin-top:20px;">
            <h3 style="margin-top:0;">Pekerja Lapangan</h3>
            <div class="worker-list">
                ${workers.map((worker) => `
                    <div class="worker-item" data-worker-id="${escapeHtml(worker.id)}">
                        <label class="worker-head">
                            <input type="checkbox" data-worker-selected>
                            <span>
                                <strong>${escapeHtml(worker.nama)}</strong><br>
                                <small>${escapeHtml(compactText(worker.no_hp, 'Tanpa nomor'))}</small>
                            </span>
                        </label>
                        <div class="worker-fields">
                            ${workerFields.filter((field) => field.name !== 'selected').map((field) => workerFieldHtml(field)).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function workerFieldHtml(field) {
    if (field.type === 'select') {
        return `
            <div class="field">
                <label>${escapeHtml(field.label)}</label>
                <select data-worker-field="${escapeHtml(field.name)}">
                    <option value="">-</option>
                    ${(field.options ?? []).map((option) => `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`).join('')}
                </select>
            </div>
        `;
    }

    const type = field.type === 'integer' || field.type === 'decimal' ? 'number' : 'text';
    const step = field.type === 'decimal' ? 'step="0.01"' : field.type === 'integer' ? 'step="1"' : '';

    return `
        <div class="field">
            <label>${escapeHtml(field.label)}</label>
            <input data-worker-field="${escapeHtml(field.name)}" data-type="${escapeHtml(field.type)}" type="${type}" ${step}>
        </div>
    `;
}

function bindAutoTotal(root) {
    const volume = qs('[data-field="volume"]', root);
    const rate = qs('[data-field="harga_satuan"]', root);
    const total = qs('[data-field="jumlah"]', root);

    if (!volume || !rate || !total) {
        return;
    }

    const sync = () => {
        const value = Number(volume.value || 0) * cleanNumber(rate.value);

        if (value > 0 && total.dataset.touched !== '1') {
            total.value = String(Math.round(value));
        }
    };

    total.addEventListener('input', () => {
        total.dataset.touched = '1';
    });
    volume.addEventListener('input', sync);
    rate.addEventListener('input', sync);
}

async function submitExpenseForm(root, form, selectedSub) {
    const message = qs('[data-form-message]', root);
    const submit = qs('button[type="submit"]', form);

    message.innerHTML = '';
    submit.disabled = true;
    submit.textContent = 'Mengirim...';

    try {
        const payload = collectPayload(root, selectedSub);
        const response = await apiRequest(selectedSub.submit_endpoint, {
            method: 'POST',
            body: payload,
        });

        message.innerHTML = `<div class="notice success">${escapeHtml(response.message ?? 'Data berhasil disimpan.')}</div>`;
        form.reset();
    } catch (error) {
        message.innerHTML = `<div class="notice error">${escapeHtml(errorMessage(error))}</div>`;
    } finally {
        submit.disabled = false;
        submit.textContent = 'Kirim Transaksi';
    }
}

function collectPayload(root, selectedSub) {
    const payload = {};

    qsa('[data-field]', root).forEach((input) => {
        const name = input.dataset.field;

        if (!name) {
            return;
        }

        if (input.type === 'checkbox') {
            payload[name] = input.checked;
            return;
        }

        payload[name] = input.dataset.type === 'money'
            ? cleanNumber(input.value)
            : input.value;
    });

    const pekerja = {};

    qsa('[data-worker-id]', root).forEach((row) => {
        const selected = qs('[data-worker-selected]', row)?.checked;

        if (!selected) {
            return;
        }

        const workerId = row.dataset.workerId;
        pekerja[workerId] = { selected: true };

        qsa('[data-worker-field]', row).forEach((input) => {
            pekerja[workerId][input.dataset.workerField] = input.dataset.type === 'money'
                ? cleanNumber(input.value)
                : input.value;
        });
    });

    if (Object.keys(pekerja).length > 0) {
        payload.pekerja = pekerja;
    }

    payload.sub_id = selectedSub.id;

    return payload;
}

function cleanNumber(value) {
    return Number(String(value ?? '').replace(/[^\d]/g, '')) || 0;
}

function errorMessage(error) {
    if (error instanceof ApiError && error.payload?.errors) {
        return Object.values(error.payload.errors).flat().join(' ');
    }

    return error.message || 'Data gagal dikirim.';
}
