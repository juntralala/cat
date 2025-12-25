export function formatDateIndonesia(date) {
    if (!date) return '';
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return date.toLocaleDateString('id-ID', options);
}