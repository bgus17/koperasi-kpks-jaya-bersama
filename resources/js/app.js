import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const moneyFormatter = new Intl.NumberFormat('id-ID');

function onlyDigits(value) {
    return String(value ?? '').replace(/\D/g, '');
}

function parseMoney(value) {
    const digits = onlyDigits(value);
    return digits ? Number(digits) : 0;
}

function parseDecimal(value) {
    const normalized = String(value ?? '')
        .replace(/[^\d,.]/g, '')
        .replace(',', '.');

    return Number.parseFloat(normalized) || 0;
}

function formatMoney(value) {
    const digits = onlyDigits(value);
    return digits ? moneyFormatter.format(Number(digits)) : '';
}

function setMoneyValue(input, value) {
    if (!input) {
        return;
    }

    input.value = value > 0 ? moneyFormatter.format(Math.round(value)) : '';
}

const activityProfiles = {
    umum: {
        title: 'Detail Operasional',
        volumeLabel: 'Volume',
        note: 'Isi volume kerja, unit barang, ritase, atau jumlah transaksi sesuai kegiatan.',
        defaultSatuan: '',
    },
    panen: {
        title: 'Aktivitas Panen',
        volumeLabel: 'Basis/Output Panen',
        note: 'Umumnya dihitung dari tonase TBS, jumlah janjang, brondolan, HK panen, dan tarif panen.',
        defaultSatuan: 'kg',
    },
    angkutan: {
        title: 'Distribusi dan Angkutan',
        volumeLabel: 'Ritase / Tonase Angkut',
        note: 'Catat tonase TBS, ritase kendaraan, nomor SPB/nota, dan vendor atau sopir.',
        defaultSatuan: 'rit',
    },
    berondol: {
        title: 'Kutip Berondol',
        volumeLabel: 'Berondolan Terkutip',
        note: 'Catat kilogram brondolan, jumlah pekerja, blok, mandor, dan tarif kutip.',
        defaultSatuan: 'kg',
    },
    perawatan: {
        title: 'Aktivitas Perawatan',
        volumeLabel: 'HK / Luas Kerja',
        note: 'Catat hektar, HK, pekerja, blok, mandor, dan tarif pekerjaan perawatan.',
        defaultSatuan: 'HK',
    },
    pupuk: {
        title: 'Pembelian / Distribusi Pupuk & Racun',
        volumeLabel: 'Jumlah Pupuk/Racun',
        note: 'Gunakan sak, kg, liter, atau ton sesuai bukti pembelian/distribusi pupuk atau racun.',
        defaultSatuan: 'sak',
    },
    alat_berat: {
        title: 'Pemakaian Alat Berat',
        volumeLabel: 'Jam Kerja / HM',
        note: 'Catat HM/jam kerja alat, lokasi blok, vendor atau operator, dan biaya per jam atau rit sesuai pekerjaan kebun.',
        defaultSatuan: 'HM',
    },
    perlengkapan: {
        title: 'Perlengkapan Operasional Kebun',
        volumeLabel: 'Jumlah Perlengkapan',
        note: 'Catat jumlah unit, set, meter, atau paket barang, supplier, nota, dan lokasi penggunaan perlengkapan kebun.',
        defaultSatuan: 'unit',
    },
    insentive: {
        title: 'Insentive Operasional',
        volumeLabel: 'Jumlah Penerima',
        note: 'Catat periode, penerima atau kelompok penerima, jumlah orang, dan nilai insentive sesuai peran di kebun.',
        defaultSatuan: 'orang',
    },
};

function resolveActivityProfile(subName = '', categoryNumber = '', detailType = '') {
    if (detailType && activityProfiles[detailType]) {
        return detailType;
    }

    const name = String(subName).toLowerCase();

    if (name.includes('panen')) return 'panen';
    if (name.includes('angkutan')) return 'angkutan';
    if (name.includes('berondol')) return 'berondol';
    if (categoryNumber === 'III') return 'perawatan';
    if (categoryNumber === 'IV' || name.includes('pupuk')) return 'pupuk';
    if (categoryNumber === 'VI' || name.includes('traktor') || name.includes('grader') || name.includes('compactor')) {
        return 'alat_berat';
    }
    if (categoryNumber === 'VII') return 'perlengkapan';
    if (categoryNumber === 'VIII') return 'insentive';

    return 'umum';
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((toggle) => {
        const target = document.getElementById(toggle.dataset.dropdown);
        const chevron = toggle.querySelector('.chevron');

        if (!target) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isOpen = target.classList.toggle('open');

            toggle.classList.toggle('active', isOpen);
            chevron?.classList.toggle('open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });
}

function initMobileSidebar() {
    const sidebar = document.querySelector('[data-mobile-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeTriggers = document.querySelectorAll('[data-sidebar-close]');

    if (!sidebar || !toggle) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 860px)');

    const syncSidebarState = () => {
        const isOpen = document.body.classList.contains('sidebar-open');

        toggle.setAttribute('aria-expanded', String(isOpen));

        if (mobileQuery.matches) {
            sidebar.setAttribute('aria-hidden', String(!isOpen));
            return;
        }

        document.body.classList.remove('sidebar-open');
        sidebar.removeAttribute('aria-hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const openSidebar = () => {
        document.body.classList.add('sidebar-open');
        syncSidebarState();
    };

    const closeSidebar = () => {
        document.body.classList.remove('sidebar-open');
        syncSidebarState();
    };

    toggle.addEventListener('click', () => {
        if (document.body.classList.contains('sidebar-open')) {
            closeSidebar();
            return;
        }

        openSidebar();
    });

    closeTriggers.forEach((trigger) => trigger.addEventListener('click', closeSidebar));

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (mobileQuery.matches) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    mobileQuery.addEventListener('change', syncSidebarState);
    syncSidebarState();
}

function initDismissibleAlerts(root = document) {
    root.querySelectorAll('[data-alert-close]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.alert')?.remove();
        });
    });
}

function initResponsiveTables(root = document) {
    const selector = '.table-wrap table, .card table';
    const tables = [
        ...(root.matches?.(selector) ? [root] : []),
        ...Array.from(root.querySelectorAll?.(selector) || []),
    ];

    tables.forEach((table) => {
        if (table.dataset.responsiveBound === '1') {
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map((header) => header.textContent.trim());

        if (headers.length === 0) {
            return;
        }

        table.querySelectorAll('tbody tr, tfoot tr').forEach((row) => {
            Array.from(row.children).forEach((cell, index) => {
                if (!cell.hasAttribute('data-label')) {
                    cell.setAttribute('data-label', headers[index] || '');
                }
            });
        });

        table.dataset.responsiveBound = '1';
    });
}

function watchResponsiveContent() {
    const content = document.querySelector('.content');

    if (!content) {
        return;
    }

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    initResponsiveTables(node);
                    initDismissibleAlerts(node);
                }
            });
        });
    });

    observer.observe(content, { childList: true, subtree: true });
}

function initRupiahInputs(root = document) {
    root.querySelectorAll('.rupiah-input').forEach((input) => {
        input.value = formatMoney(input.value);

        input.addEventListener('input', () => {
            input.value = formatMoney(input.value);
        });

        const form = input.closest('form');

        if (form && !form.dataset.rupiahCleanBound) {
            form.dataset.rupiahCleanBound = '1';
            form.addEventListener('submit', () => {
                form.querySelectorAll('.rupiah-input').forEach((field) => {
                    field.value = parseMoney(field.value);
                });
            });
        }
    });
}

function profileMatches(element, profile) {
    const profiles = (element.dataset.profiles || element.dataset.workerProfiles || '')
        .split(/\s+/)
        .filter(Boolean);

    return profiles.length === 0 || profiles.includes(profile);
}

function selectedOption(select) {
    return select?.selectedOptions?.[0] ?? null;
}

function initActivityForms() {
    document.querySelectorAll('[data-activity-form]').forEach((form) => {
        const categorySelect = form.querySelector('[data-kategori-select]');
        const subSelect = form.querySelector('[data-sub-select]');
        const unitSelect = form.querySelector('[data-satuan-select]');
        const volumeInput = form.querySelector('[data-cost-volume]');
        const rateInput = form.querySelector('[data-cost-rate]');
        const totalInput = form.querySelector('[data-cost-total]');
        const title = form.querySelector('[data-profile-title]');
        const note = form.querySelector('[data-profile-note]');
        const volumeLabel = form.querySelector('[data-volume-label]');
        const workerSearch = form.querySelector('[data-worker-search]');
        const workerRows = Array.from(form.querySelectorAll('[data-worker-row]'));

        function currentProfile() {
            const subOption = selectedOption(subSelect);
            const categoryOption = selectedOption(categorySelect);

            return resolveActivityProfile(
                subOption?.dataset.subName || form.dataset.initialSub || '',
                subOption?.dataset.categoryNumber || categoryOption?.dataset.categoryNumber || form.dataset.initialCategory || '',
                subOption?.dataset.detailType || form.dataset.initialDetailType || form.dataset.initialProfile || '',
            );
        }

        function syncSubOptions() {
            if (!categorySelect || !subSelect) {
                return;
            }

            const selectedCategory = categorySelect.value;
            let hasSelectedVisible = false;

            Array.from(subSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const visible = !selectedCategory || option.dataset.kategoriId === selectedCategory;
                option.hidden = !visible;

                if (option.selected && visible) {
                    hasSelectedVisible = true;
                }
            });

            if (!hasSelectedVisible) {
                subSelect.value = '';
            }
        }

        function updateProfile() {
            const profile = currentProfile();
            const config = activityProfiles[profile] || activityProfiles.umum;

            if (title) title.textContent = config.title;
            if (note) note.textContent = config.note;
            if (volumeLabel) volumeLabel.textContent = config.volumeLabel;

            if (unitSelect && !unitSelect.value && config.defaultSatuan) {
                unitSelect.value = config.defaultSatuan;
            }

            form.querySelectorAll('.activity-field').forEach((field) => {
                field.hidden = !profileMatches(field, profile);
            });

            form.querySelectorAll('.worker-field').forEach((field) => {
                field.hidden = !profileMatches(field, profile);
            });

            recalculateWorkers();
            calculateTotal();
        }

        function selectedWorkers() {
            return workerRows.filter((row) => row.querySelector('[data-worker-toggle]')?.checked);
        }

        function workerBase(row) {
            const profile = currentProfile();

            if (profile === 'panen' || profile === 'angkutan') {
                return parseDecimal(row.querySelector('[data-worker-tonase]')?.value);
            }

            if (profile === 'berondol') {
                return parseDecimal(row.querySelector('[data-worker-brondolan]')?.value);
            }

            if (profile === 'perawatan') {
                return parseDecimal(row.querySelector('[data-worker-hk]')?.value)
                    || parseDecimal(row.querySelector('[data-worker-luas]')?.value)
                    || parseDecimal(row.querySelector('[data-worker-volume]')?.value);
            }

            return parseDecimal(row.querySelector('[data-worker-volume]')?.value)
                || parseDecimal(row.querySelector('[data-worker-tonase]')?.value);
        }

        function calculateWorkerPay(row) {
            const upahInput = row.querySelector('[data-worker-upah]');
            const rate = parseMoney(row.querySelector('[data-worker-rate]')?.value);
            const base = workerBase(row);

            if (!upahInput || rate <= 0 || base <= 0) {
                return;
            }

            setMoneyValue(upahInput, base * rate);
        }

        function calculateTotal() {
            if (!volumeInput || !rateInput || !totalInput || selectedWorkers().length > 0) {
                return;
            }

            const volume = parseDecimal(volumeInput.value);
            const rate = parseMoney(rateInput.value);

            if (volume > 0 && rate > 0) {
                setMoneyValue(totalInput, volume * rate);
            }
        }

        function syncWorkerState(row) {
            const checked = row.querySelector('[data-worker-toggle]')?.checked ?? false;
            row.classList.toggle('is-selected', checked);
        }

        function recalculateWorkers() {
            const selected = selectedWorkers();
            const sum = (selector, parser = parseDecimal) => selected.reduce((total, row) => {
                const input = row.querySelector(selector);

                if (!input || input.closest('[hidden]')) {
                    return total;
                }

                return total + parser(input.value);
            }, 0);

            const totalTonase = sum('[data-worker-tonase]');
            const totalJanjang = sum('[data-worker-janjang]');
            const totalBrondolan = sum('[data-worker-brondolan]');
            const totalLuas = sum('[data-worker-luas]');
            const totalVolume = selected.reduce((total, row) => total + workerBase(row), 0);
            const totalUpah = sum('[data-worker-upah]', parseMoney);

            const workerCount = form.querySelector('[data-worker-count]');
            const workerSummary = form.querySelector('[data-worker-summary]');

            if (workerCount) {
                workerCount.value = selected.length;
            }

            if (workerSummary) {
                workerSummary.textContent = `${selected.length} pekerja dipilih`;
            }

            const setNumber = (selector, value, decimals = 2) => {
                const input = form.querySelector(selector);

                if (input && selected.length > 0) {
                    input.value = Number.isInteger(value) ? value : value.toFixed(decimals).replace(/\.?0+$/, '');
                }
            };

            setNumber('[data-header-tonase]', totalTonase);
            setNumber('[data-header-janjang]', totalJanjang, 0);
            setNumber('[data-header-brondolan]', totalBrondolan);
            setNumber('[data-header-luas]', totalLuas);
            setNumber('[data-header-volume]', totalVolume || totalTonase || totalBrondolan || totalLuas);

            if (totalUpah > 0) {
                setMoneyValue(totalInput, totalUpah);
            }

            if (totalInput) {
                totalInput.readOnly = selected.length > 0;
            }
        }

        categorySelect?.addEventListener('change', () => {
            syncSubOptions();
            updateProfile();
        });

        subSelect?.addEventListener('change', updateProfile);

        workerRows.forEach((row) => {
            syncWorkerState(row);

            row.querySelector('[data-worker-toggle]')?.addEventListener('change', () => {
                syncWorkerState(row);
                recalculateWorkers();
            });

            row.querySelectorAll('input, select').forEach((input) => {
                input.addEventListener('input', () => {
                    calculateWorkerPay(row);
                    recalculateWorkers();
                });
                input.addEventListener('change', () => {
                    calculateWorkerPay(row);
                    recalculateWorkers();
                });
            });
        });

        volumeInput?.addEventListener('input', calculateTotal);
        rateInput?.addEventListener('input', calculateTotal);

        workerSearch?.addEventListener('input', () => {
            const query = workerSearch.value.trim().toLowerCase();

            workerRows.forEach((row) => {
                row.hidden = Boolean(query) && !(row.dataset.workerName || '').includes(query);
            });
        });

        syncSubOptions();
        updateProfile();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDropdowns();
    initMobileSidebar();
    initDismissibleAlerts();
    initResponsiveTables();
    watchResponsiveContent();
    initRupiahInputs();
    initActivityForms();
});
