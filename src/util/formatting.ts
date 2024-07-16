export const formatGold = (gold: number) => {
    return gold.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

export const formatDate = (date: string) => {
    // Format the date as "DD-MM-YYYY"
    const d = new Date(date);
    return `${d.getDate()}-${d.getMonth() + 1}-${d.getFullYear()}`;
};
