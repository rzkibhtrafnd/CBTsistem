const jakartaTimeZone = 'Asia/Jakarta';

export function formatIndonesiaDateTime(value, { dateStyle = 'medium', timeStyle = 'short' } = {}) {
    if (!value) return '—';

    const formatted = new Intl.DateTimeFormat('id-ID', {
        dateStyle,
        timeStyle,
        timeZone: jakartaTimeZone,
    }).format(new Date(value));

    return `${formatted} WIB`;
}

export function toJakartaDateTimeLocal(value) {
    if (!value) return '';

    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: jakartaTimeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(new Date(value));
    const dateParts = Object.fromEntries(parts.map(({ type, value: partValue }) => [type, partValue]));

    return `${dateParts.year}-${dateParts.month}-${dateParts.day}T${dateParts.hour}:${dateParts.minute}`;
}
