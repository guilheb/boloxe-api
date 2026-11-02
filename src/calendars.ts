import type { Env } from './env';

export interface Calendar {
    id: string;
    name: string | null;
    color: string;
    url: string;
}

// Used in order for calendars without a CAL_<ID>_COLOR (Apple Calendar's palette)
const DEFAULT_COLORS = ['#1badf8', '#ffcc00', '#63da38', '#ff2968', '#cc73e1', '#ff9500', '#a2845e'];

const HTTP_TIMEOUT = 15_000;

/**
 * Subscribed calendars are declared only in the environment: every CAL_<ID>_URL is one calendar,
 * with optional CAL_<ID>_NAME and CAL_<ID>_COLOR, sorted by ID.
 */
export class Calendars {
    readonly #calendars: Calendar[];

    constructor(env: Env) {
        // CAL_<ID>_URL variables that are set → <ID>
        const keys = Object.keys(env)
            .filter(name => /^CAL_[A-Z0-9_]+_URL$/.test(name) && typeof env[name] === 'string' && env[name] !== '')
            .map(name => name.slice(4, -4))
            .sort();

        this.#calendars = keys.map((key, i) => ({
            id: key.toLowerCase(),
            name: stringOrNull(env[`CAL_${key}_NAME`]),
            color: stringOrNull(env[`CAL_${key}_COLOR`]) ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length]!,
            url: env[`CAL_${key}_URL`] as string,
        }));
    }

    // The feed URLs are left out: they must not leave the server
    get all(): Omit<Calendar, 'url'>[] {
        return this.#calendars.map(({ id, name, color }) => ({ id, name, color }));
    }

    get(id: string): Calendar | undefined {
        return this.#calendars.find(calendar => calendar.id === id);
    }

    /**
     * Downloads the calendar's ICS feed. The URL is kept out of the error messages: it is private.
     */
    async fetch(calendar: Calendar): Promise<string> {
        // webcal:// links become https:// (the URL protocol setter can't switch to a special scheme)
        const url = calendar.url.replace(/^webcals?:/i, 'https:');

        let response: Response;

        try {
            response = await fetch(url, { signal: AbortSignal.timeout(HTTP_TIMEOUT) });
        }
        catch (e) {
            throw new Error(`HTTP request failed: ${e instanceof Error ? e.message : e}`);
        }

        if (response.status !== 200) {
            throw new Error(`HTTP ${response.status}`);
        }

        const ics = await response.text();

        if (!ics.trimStart().startsWith('BEGIN:VCALENDAR')) {
            throw new Error('Response is not an iCalendar feed');
        }

        return ics;
    }
}

function stringOrNull(value: unknown): string | null {
    return typeof value === 'string' && value !== '' ? value : null;
}
