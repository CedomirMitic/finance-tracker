// resources/js/Utils/formatters.ts
export const formatCurrency = (value: number, currency = 'EUR') => {
    const formatter = new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency,
    });

    const parts = formatter.formatToParts(value);
    
    return parts.map(part => {
        if (part.type === 'currency') return `${part.value} `;
        return part.value;
    }).join('');
};