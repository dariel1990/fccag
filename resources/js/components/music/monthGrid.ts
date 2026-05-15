export type WeekCell = {
    date: string;
    day: number;
    dayOfWeek: number;
    inMonth: boolean;
};

export const WEEKDAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as const;

export const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
] as const;

/**
 * Build a Sunday-start week grid (UTC) for the given year/month. Pads leading
 * days from the previous month and trailing days from the next month so each
 * returned row always has exactly 7 cells.
 */
export function buildWeeks(year: number, month: number): WeekCell[][] {
    const first = new Date(Date.UTC(year, month - 1, 1));
    const last = new Date(Date.UTC(year, month, 0));
    const startOffset = first.getUTCDay();
    const totalDays = last.getUTCDate();

    const cells: WeekCell[] = [];

    for (let i = 0; i < startOffset; i++) {
        const d = new Date(Date.UTC(year, month - 1, -startOffset + i + 1));
        cells.push({
            date: d.toISOString().slice(0, 10),
            day: d.getUTCDate(),
            dayOfWeek: d.getUTCDay(),
            inMonth: false,
        });
    }

    for (let day = 1; day <= totalDays; day++) {
        const d = new Date(Date.UTC(year, month - 1, day));
        cells.push({
            date: d.toISOString().slice(0, 10),
            day,
            dayOfWeek: d.getUTCDay(),
            inMonth: true,
        });
    }

    while (cells.length % 7 !== 0) {
        const lastCell = cells[cells.length - 1];
        const [yy, mm, dd] = lastCell.date.split('-').map(Number);
        const next = new Date(Date.UTC(yy, mm - 1, dd + 1));
        cells.push({
            date: next.toISOString().slice(0, 10),
            day: next.getUTCDate(),
            dayOfWeek: next.getUTCDay(),
            inMonth: false,
        });
    }

    const out: WeekCell[][] = [];
    for (let i = 0; i < cells.length; i += 7) {
        out.push(cells.slice(i, i + 7));
    }
    return out;
}

/**
 * Returns today's date as YYYY-MM-DD using the user's local timezone, suitable
 * for matching against service_date strings produced by the calendar grid.
 */
export function todayKey(): string {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
