export function qs(selector, root = document) {
    return root.querySelector(selector);
}

export function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

export function rupiah(value) {
    const number = Number(value ?? 0);

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number.isFinite(number) ? number : 0);
}

export function compactText(value, fallback = '-') {
    const text = String(value ?? '').trim();

    return text === '' ? fallback : text;
}

export function setDocumentTitle(title) {
    document.title = `${title} - Portal User Koperasi`;
}
