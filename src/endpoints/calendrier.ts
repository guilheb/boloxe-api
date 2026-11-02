import type { Env } from '../env';
import { Calendars } from '../calendars';
import { withCors } from '../cors';

const encoder = new TextEncoder();

// The CORS check comes before the key check: the preflight request doesn't carry the key
export function calendrier(request: Request, env: Env): Promise<Response> {
    return withCors(request, async () => {
        if (!isKeyValid(env.INTERNAL_API_KEY ?? '', request.headers.get('X-Internal-Api-Key') ?? '')) {
            return new Response(null, { status: 401 });
        }

        const headers = new Headers({ 'Cache-Control': 'no-store' });
        const calendars = new Calendars(env);
        const id = new URL(request.url).searchParams.get('id');

        // Without an id: the calendar list (id, name, color, never the URLs)
        if (id === null) {
            headers.set('Content-Type', 'application/json; charset=utf-8');
            return new Response(JSON.stringify(calendars.all), { headers });
        }

        const calendar = calendars.get(id);

        if (calendar === undefined) {
            return new Response(null, { status: 404, headers });
        }

        let ics: string;

        try {
            ics = await calendars.fetch(calendar);
        }
        catch (e) {
            console.error(`[calendrier] ${calendar.id}: ${e instanceof Error ? e.message : e}`);
            return new Response(null, { status: 502, headers });
        }

        headers.set('Content-Type', 'text/calendar; charset=utf-8');
        return new Response(ics, { headers });
    }, ['X-Internal-Api-Key']);
}

// Constant-time comparison, refused as long as the expected key isn't set
function isKeyValid(expectedKey: string, givenKey: string): boolean {
    const expected = encoder.encode(expectedKey);
    const given = encoder.encode(givenKey);

    return expected.byteLength > 0
        && expected.byteLength === given.byteLength
        && crypto.subtle.timingSafeEqual(expected, given);
}
