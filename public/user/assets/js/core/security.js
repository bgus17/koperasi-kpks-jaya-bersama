export function securityContext() {
    const isLocal = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
    const isHttps = window.location.protocol === 'https:';

    return {
        isLocal,
        isHttps,
        isSecureEnough: isHttps || isLocal,
        warning: isHttps || isLocal
            ? null
            : 'Koneksi belum HTTPS. Untuk data keuangan, jalankan portal melalui HTTPS di produksi.',
    };
}
