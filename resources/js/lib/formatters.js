export function formatDateIndonesia(date) {
    if (!date) return '';
    if (typeof date == 'string') return date;
    const options = {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
    };
    return date.toLocaleDateString('id-ID', {options});
}