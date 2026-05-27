const FORM_MENU_TYPES = new Set(['pengeluaran', 'rekap']);

export function menuHref(menu) {
    return isFormMenu(menu) ? `#/form/${menu.slug}` : '#/dashboard';
}

export function isFormMenu(menu) {
    return FORM_MENU_TYPES.has(menu?.type);
}
