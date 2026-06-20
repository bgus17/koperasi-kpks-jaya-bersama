import { apiRequest, ApiError } from '../core/http.js';
import { compactText, escapeHtml, qs, qsa, setDocumentTitle } from '../core/dom.js';
import { renderShell } from './shell.js';
import { bindRekap } from './rekap.js';

export async function renderFormPage(root, slug) {
    setDocumentTitle('Form');
    renderShell(root, '<div class="notice">Memuat form...</div>', slug);

    try {
        const payload = await apiRequest(`/aktor/forms/${encodeURIComponent(slug)}`);

        if (payload.form?.mode === 'read_only') {
            renderReadOnlyForm(root, payload);
            return;
        }

        renderExpenseForm(root, payload);
    } catch (error) {
        renderShell(root, `<div class="notice error">${escapeHtml(error.message)}</div>`, slug);
    }
}

function renderReadOnlyForm(root, payload) {
    const year = new Date().getFullYear();
    const months = [
        ['1', 'Januari'],
        ['2', 'Februari'],
        ['3', 'Maret'],
        ['4', 'April'],
        ['5', 'Mei'],
        ['6', 'Juni'],
        ['7', 'Juli'],
        ['8', 'Agustus'],
        ['9', 'September'],
        ['10', 'Oktober'],
        ['11', 'November'],
        ['12', 'Desember'],
    ];

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
                <div class="field">
                    <label for="rekap-month">Periode</label>
                    <select id="rekap-month" data-rekap-month>
                        <option value="">Tahunan</option>
                        ${months.map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}
                    </select>
                </div>
                <div class="field form-action-field">
                    <button class="btn btn-primary" type="button" data-load-rekap>Load Rekap</button>
                </div>
            </div>
            <div class="result-space" data-rekap-result></div>
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
            <h2 class="section-title">Kegiatan</h2>
            <div class="sub-list" data-sub-list>
                ${(payload.sub_options ?? []).map((sub) => subButton(sub, firstSub?.id)).join('')}
            </div>
        </div>
        <form class="form-card" data-expense-form>
            <div data-form-message></div>
            <div data-dynamic-form>
                ${formHtmlForProfile(payload, selectedProfile)}
            </div>
            <div class="btn-row form-actions">
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
            bindInteractiveFormEvents(root);
        });
    });

    bindInteractiveFormEvents(root);

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
                <h2 class="toolbar-title">${escapeHtml(schema?.title ?? 'Detail Operasional')}</h2>
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
            <div class="field field-full">
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
        <div class="panel worker-panel">
            <h3 class="section-title">Pekerja Lapangan</h3>
            <div class="worker-toolbar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap: 10px; flex-wrap: wrap;">
                <input type="text" data-worker-search placeholder="Cari nama karyawan..." style="max-width: 300px; width: 100%; padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-size:13px; color:var(--hijau); outline:none;">
                <span data-worker-summary style="font-size:12.5px; color:var(--abu); font-weight:600;">0 pekerja dipilih</span>
            </div>
            <div class="worker-list">
                ${workers.map((worker) => `
                    <div class="worker-item" data-worker-id="${escapeHtml(worker.id)}" data-worker-name="${escapeHtml(String(worker.nama).toLowerCase())}">
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

function bindInteractiveFormEvents(root) {
    const form = qs('[data-expense-form]', root);
    if (!form) return;

    const mainVolume = qs('[data-field="volume"]', root);
    const mainRate = qs('[data-field="harga_satuan"]', root);
    const mainTotal = qs('[data-field="jumlah"]', root);

    const workerItems = qsa('[data-worker-id]', root);
    const workerSearch = qs('[data-worker-search]', root);
    const workerSummary = qs('[data-worker-summary]', root);

    const getSelectedWorkers = () => {
        return Array.from(workerItems).filter(item => {
            return qs('[data-worker-selected]', item)?.checked;
        });
    };

    const getWorkerBase = (item) => {
        const vol = qs('[data-worker-field="volume"]', item)?.value || '';
        const hk = qs('[data-worker-field="hk"]', item)?.value || '';
        const luas = qs('[data-worker-field="luas_ha"]', item)?.value || '';
        return Number(vol || hk || luas || 0);
    };

    const getWorkerRate = (item) => {
        const rateInput = qs('[data-worker-field="tarif_satuan"]', item);
        return cleanNumber(rateInput?.value || 0);
    };

    const calculateWorkerPay = (item) => {
        const upahInput = qs('[data-worker-field="upah"]', item);
        if (!upahInput) return;

        const base = getWorkerBase(item);
        const rate = getWorkerRate(item);
        if (base > 0 && rate > 0) {
            upahInput.value = String(Math.round(base * rate));
        }
    };

    const recalculateGrandTotals = () => {
        const selected = getSelectedWorkers();
        if (workerSummary) {
            workerSummary.textContent = `${selected.length} pekerja dipilih`;
        }

        if (selected.length > 0) {
            let totalVolumeValue = 0;
            let totalUpahValue = 0;

            selected.forEach(item => {
                totalVolumeValue += getWorkerBase(item);
                const upahInput = qs('[data-worker-field="upah"]', item);
                totalUpahValue += cleanNumber(upahInput?.value || 0);
            });

            if (mainVolume) {
                mainVolume.value = totalVolumeValue > 0 ? String(totalVolumeValue) : '';
            }
            if (mainTotal) {
                mainTotal.value = totalUpahValue > 0 ? String(totalUpahValue) : '';
                mainTotal.readOnly = true;
            }
        } else {
            if (mainTotal) {
                mainTotal.readOnly = false;
            }
        }
    };

    // Bind worker rows interactions
    workerItems.forEach(item => {
        const checkbox = qs('[data-worker-selected]', item);
        
        const syncRowState = () => {
            const isChecked = checkbox?.checked ?? false;
            item.classList.toggle('is-selected', isChecked);
        };

        if (checkbox) {
            // Initial sync
            syncRowState();

            checkbox.addEventListener('change', () => {
                syncRowState();
                recalculateGrandTotals();
            });
        }

        // Bind worker inputs changes
        item.querySelectorAll('input, select').forEach(input => {
            if (input !== checkbox) {
                const handleInput = () => {
                    calculateWorkerPay(item);
                    recalculateGrandTotals();
                };
                input.addEventListener('input', handleInput);
                input.addEventListener('change', handleInput);
            }
        });
    });

    // Bind worker search
    if (workerSearch) {
        workerSearch.addEventListener('input', () => {
            const query = workerSearch.value.trim().toLowerCase();
            workerItems.forEach(item => {
                const name = item.dataset.workerName || '';
                item.style.display = !query || name.includes(query) ? '' : 'none';
            });
        });
    }

    // Bind main auto total (when no workers selected)
    if (mainVolume && mainRate && mainTotal) {
        const syncMainTotal = () => {
            const selected = getSelectedWorkers();
            if (selected.length === 0) {
                const vol = Number(mainVolume.value || 0);
                const rate = cleanNumber(mainRate.value);
                if (vol > 0 && rate > 0 && mainTotal.dataset.touched !== '1') {
                    mainTotal.value = String(Math.round(vol * rate));
                }
            }
        };

        mainTotal.addEventListener('input', () => {
            mainTotal.dataset.touched = '1';
        });
        mainVolume.addEventListener('input', syncMainTotal);
        mainRate.addEventListener('input', syncMainTotal);
    }
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
