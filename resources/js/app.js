/**
 * KOPERASI CAHAYA MULYA — Global JavaScript
 * File: resources/js/app.js
 * Di-bundle oleh Vite → public/build/assets/app-*.js
 */

import './bootstrap';

/* ==========================================================================
   SIDEBAR DROPDOWN
   ========================================================================== */

/**
 * Toggle dropdown menu sidebar (Pengeluaran & Biaya, dsb.).
 * @param {string} id  — ID elemen dropdown, contoh: 'drop-pengeluaran'
 */
function toggleDropdown(id) {
    const el      = document.getElementById(id);
    const toggleId = 'toggle-' + id.replace('drop-', '');
    const toggle  = document.getElementById(toggleId);

    if (!el || !toggle) return;

    const chevron = toggle.querySelector('.chevron');
    const isOpen  = el.classList.contains('open');

    el.classList.toggle('open', !isOpen);
    chevron?.classList.toggle('open', !isOpen);
}

// Expose ke window agar bisa diakses dari inline onclick (fallback)
window.toggleDropdown = toggleDropdown;

// Setup dropdown toggles dengan event listeners
function setupDropdownToggles() {
    document.querySelectorAll('[data-dropdown]').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const dropdownId = toggle.getAttribute('data-dropdown');
            if (dropdownId) {
                toggleDropdown(dropdownId);
            }
        });
    });
}

// Inisialisasi saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupDropdownToggles);
} else {
    // DOM sudah siap (jika script diload di akhir <body>)
    setupDropdownToggles();
}

/* ==========================================================================
   AUTO-OPEN MENU AKTIF
   Membuka dropdown otomatis jika rute halaman saat ini berada di bawah menu.
   Nilai `window.activeDropdowns` di-set oleh layout blade.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    const active = window.activeDropdowns || [];
    active.forEach(id => {
        const el      = document.getElementById(id);
        const toggleId = 'toggle-' + id.replace('drop-', '');
        const toggle  = document.getElementById(toggleId);

        if (!el || !toggle) return;
        el.classList.add('open');
        toggle.querySelector('.chevron')?.classList.add('open');
    });
});

/* ==========================================================================
   RUPIAH INPUT FORMATTER
   Memformat input angka menjadi format ribuan Indonesia (1.000.000).
   Selector: elemen dengan class `.rupiah-input`.

   Cara pakai di blade:
     <input type="text" name="debet" class="rupiah-input" value="...">
   ========================================================================== */

/**
 * Mengembalikan string tanpa karakter non-angka.
 * @param {string} value
 * @returns {number}
 */
function getRawValue(value) {
    return parseInt(String(value).replace(/\D/g, '')) || 0;
}

/**
 * Format angka ke string Rupiah tanpa simbol "Rp".
 * Contoh: 1500000 → "1.500.000"
 * @param {number} value
 * @returns {string}
 */
function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID').format(value);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.rupiah-input').forEach(input => {
        // Format nilai awal (mis. saat halaman edit)
        if (input.value) {
            input.value = formatRupiah(getRawValue(input.value));
        }

        // Format ulang saat user mengetik
        input.addEventListener('input', function () {
            const raw = getRawValue(this.value);
            this.value = formatRupiah(raw);
        });

        // Bersihkan kembali ke angka murni sebelum form dikirim
        const form = input.closest('form');
        if (form) {
            // Gunakan once:false agar handler terpasang sekali per form
            if (!form.dataset.rupiahCleanBound) {
                form.dataset.rupiahCleanBound = '1';
                form.addEventListener('submit', () => {
                    form.querySelectorAll('.rupiah-input').forEach(field => {
                        field.value = getRawValue(field.value);
                    });
                });
            }
        }
    });
});

/* ==========================================================================
   FORM PENGELUARAN OPERASIONAL SAWIT
   Menyesuaikan field lapangan dan menghitung jumlah biaya dari pekerja.
   ========================================================================== */

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
    if (detailType && activityProfiles[detailType]) return detailType;

    const name = String(subName).toLowerCase();

    if (name.includes('panen')) return 'panen';
    if (name.includes('angkutan')) return 'angkutan';
    if (name.includes('berondol')) return 'berondol';
    if (categoryNumber === 'III') return 'perawatan';
    if (categoryNumber === 'IV' || name.includes('pupuk')) return 'pupuk';
    if (categoryNumber === 'VI' || name.includes('traktor') || name.includes('grader') || name.includes('compactor')) return 'alat_berat';
    if (categoryNumber === 'VII') return 'perlengkapan';
    if (categoryNumber === 'VIII') return 'insentive';

    return 'umum';
}

function parseDecimalInput(value) {
    const normalized = String(value || '').replace(',', '.').replace(/[^0-9.]/g, '');
    return parseFloat(normalized) || 0;
}

function setupActivityForms() {
    document.querySelectorAll('[data-activity-form]').forEach(form => {
        const kategoriSelect = form.querySelector('[data-kategori-select]');
        const subSelect = form.querySelector('[data-sub-select]');
        const satuanSelect = form.querySelector('[data-satuan-select]');
        const volumeInput = form.querySelector('[data-cost-volume]');
        const rateInput = form.querySelector('[data-cost-rate]');
        const totalInput = form.querySelector('[data-cost-total]');
        const titleEl = form.querySelector('[data-profile-title]');
        const noteEl = form.querySelector('[data-profile-note]');
        const volumeLabel = form.querySelector('[data-volume-label]');
        const workerRows = Array.from(form.querySelectorAll('[data-worker-row]'));
        const workerSearch = form.querySelector('[data-worker-search]');
        const workerSummary = form.querySelector('[data-worker-summary]');
        const workerCountInput = form.querySelector('[data-worker-count]');
        const headerTonase = form.querySelector('[data-header-tonase]');
        const headerJanjang = form.querySelector('[data-header-janjang]');
        const headerBrondolan = form.querySelector('[data-header-brondolan]');
        const headerLuas = form.querySelector('[data-header-luas]');
        const headerVolume = form.querySelector('[data-header-volume]');
        let currentProfile = form.dataset.initialProfile || 'umum';

        const selectedCategoryNumber = () => {
            if (subSelect?.selectedOptions?.[0]?.dataset.categoryNumber) {
                return subSelect.selectedOptions[0].dataset.categoryNumber;
            }

            if (kategoriSelect?.selectedOptions?.[0]?.dataset.categoryNumber) {
                return kategoriSelect.selectedOptions[0].dataset.categoryNumber;
            }

            return form.dataset.initialCategory || '';
        };

        const selectedSubName = () => {
            if (subSelect?.selectedOptions?.[0]?.dataset.subName) {
                return subSelect.selectedOptions[0].dataset.subName;
            }

            return form.dataset.initialSub || '';
        };

        const selectedDetailType = () => {
            if (subSelect?.selectedOptions?.[0]?.dataset.detailType) {
                return subSelect.selectedOptions[0].dataset.detailType;
            }

            return form.dataset.initialDetailType || '';
        };

        const filterSubOptions = () => {
            if (!kategoriSelect || !subSelect) return;

            const kategoriId = kategoriSelect.value;
            let selectedStillValid = false;

            subSelect.querySelectorAll('option[data-kategori-id]').forEach(option => {
                const isMatch = !kategoriId || option.dataset.kategoriId === kategoriId;
                option.hidden = !isMatch;
                option.disabled = !isMatch;

                if (option.selected && isMatch) {
                    selectedStillValid = true;
                }
            });

            if (!selectedStillValid) {
                subSelect.value = '';
            }
        };

        const applyProfile = () => {
            const profileKey = resolveActivityProfile(selectedSubName(), selectedCategoryNumber(), selectedDetailType());
            const config = activityProfiles[profileKey] || activityProfiles.umum;
            currentProfile = profileKey;

            if (titleEl) titleEl.textContent = config.title;
            if (noteEl) noteEl.textContent = config.note;
            if (volumeLabel) volumeLabel.textContent = config.volumeLabel;

            form.querySelectorAll('[data-profiles]').forEach(field => {
                const profiles = field.dataset.profiles.split(/\s+/);
                field.classList.toggle('is-hidden', !profiles.includes(profileKey));
            });

            form.querySelectorAll('[data-worker-profiles]').forEach(field => {
                const profiles = field.dataset.workerProfiles.split(/\s+/);
                field.classList.toggle('is-hidden', !profiles.includes(profileKey));
            });

            if (satuanSelect && !satuanSelect.value && config.defaultSatuan) {
                satuanSelect.value = config.defaultSatuan;
            }

            updateWorkerTotals();
        };

        const calculateTotal = () => {
            if (!volumeInput || !rateInput || !totalInput) return;

            if (selectedWorkerRows().length > 0) return;

            const volume = parseDecimalInput(volumeInput.value);
            const rate = getRawValue(rateInput.value);

            if (volume <= 0 || rate <= 0) return;

            totalInput.value = formatRupiah(Math.round(volume * rate));
        };

        const selectedWorkerRows = () => workerRows.filter(row => row.querySelector('[data-worker-toggle]')?.checked);

        const getWorkerBase = (row) => {
            if (currentProfile === 'panen' || currentProfile === 'angkutan') {
                return parseDecimalInput(row.querySelector('[data-worker-tonase]')?.value);
            }

            if (currentProfile === 'berondol') {
                return parseDecimalInput(row.querySelector('[data-worker-brondolan]')?.value);
            }

            if (currentProfile === 'perawatan') {
                return parseDecimalInput(row.querySelector('[data-worker-hk]')?.value)
                    || parseDecimalInput(row.querySelector('[data-worker-luas]')?.value)
                    || parseDecimalInput(row.querySelector('[data-worker-volume]')?.value);
            }

            return parseDecimalInput(row.querySelector('[data-worker-volume]')?.value)
                || parseDecimalInput(row.querySelector('[data-worker-tonase]')?.value);
        };

        const setNumericValue = (input, value, fractionDigits = 2) => {
            if (!input) return;
            input.value = value > 0 ? Number(value).toFixed(fractionDigits).replace(/\.00$/, '') : '';
        };

        const setWorkerRowState = (row) => {
            const checked = row.querySelector('[data-worker-toggle]')?.checked || false;
            row.classList.toggle('is-selected', checked);
        };

        const recalculateWorkerUpah = (row) => {
            const base = getWorkerBase(row);
            const rate = getRawValue(row.querySelector('[data-worker-rate]')?.value);
            const upahInput = row.querySelector('[data-worker-upah]');

            if (!upahInput || base <= 0 || rate <= 0) return;

            upahInput.value = formatRupiah(Math.round(base * rate));
        };

        function updateWorkerTotals() {
            let count = 0;
            let totalUpah = 0;
            let totalTonase = 0;
            let totalJanjang = 0;
            let totalBrondolan = 0;
            let totalLuas = 0;
            let totalVolume = 0;

            selectedWorkerRows().forEach(row => {
                count += 1;
                totalUpah += getRawValue(row.querySelector('[data-worker-upah]')?.value);
                totalTonase += parseDecimalInput(row.querySelector('[data-worker-tonase]')?.value);
                totalJanjang += parseDecimalInput(row.querySelector('[data-worker-janjang]')?.value);
                totalBrondolan += parseDecimalInput(row.querySelector('[data-worker-brondolan]')?.value);
                totalLuas += parseDecimalInput(row.querySelector('[data-worker-luas]')?.value);
                totalVolume += getWorkerBase(row);
            });

            if (workerCountInput) workerCountInput.value = count || '';
            if (workerSummary) workerSummary.textContent = count + ' pekerja dipilih';

            if (count > 0) {
                if (totalInput) {
                    totalInput.value = formatRupiah(totalUpah);
                    totalInput.readOnly = true;
                }

                setNumericValue(headerTonase, totalTonase);
                setNumericValue(headerJanjang, totalJanjang, 0);
                setNumericValue(headerBrondolan, totalBrondolan);
                setNumericValue(headerLuas, totalLuas);
                setNumericValue(headerVolume, totalVolume);
            } else if (totalInput) {
                totalInput.readOnly = false;
            }
        }

        workerRows.forEach(row => {
            setWorkerRowState(row);

            row.querySelector('[data-worker-toggle]')?.addEventListener('change', () => {
                setWorkerRowState(row);
                updateWorkerTotals();
            });

            row.querySelectorAll('[data-worker-tonase], [data-worker-brondolan], [data-worker-hk], [data-worker-luas], [data-worker-volume], [data-worker-rate]')
                .forEach(input => {
                    input.addEventListener('input', () => {
                        recalculateWorkerUpah(row);
                        updateWorkerTotals();
                    });
                });

            row.querySelector('[data-worker-janjang]')?.addEventListener('input', updateWorkerTotals);
            row.querySelector('[data-worker-upah]')?.addEventListener('input', updateWorkerTotals);
        });

        workerSearch?.addEventListener('input', () => {
            const term = workerSearch.value.trim().toLowerCase();
            workerRows.forEach(row => {
                const matches = !term || row.dataset.workerName.includes(term);
                row.style.display = matches ? '' : 'none';
            });
        });

        kategoriSelect?.addEventListener('change', () => {
            filterSubOptions();
            applyProfile();
        });

        subSelect?.addEventListener('change', applyProfile);
        volumeInput?.addEventListener('input', calculateTotal);
        rateInput?.addEventListener('input', calculateTotal);

        filterSubOptions();
        applyProfile();
        calculateTotal();
        updateWorkerTotals();
    });
}

document.addEventListener('DOMContentLoaded', setupActivityForms);
