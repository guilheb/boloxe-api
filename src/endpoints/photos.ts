import type { Env } from '../env';
import { withCors } from '../cors';

export const PHOTOS_CACHE_KEY = 'boloxe_portfolio_albums';

export function photos(request: Request, env: Env): Promise<Response> {
    return withCors(request, async () => {
        let albums: string | null;

        try {
            // Already JSON: stored as is by the cron trigger
            albums = await env.CACHE.get(PHOTOS_CACHE_KEY);
        }
        catch {
            return new Response(null, { status: 503 });
        }

        return new Response(albums ?? 'null', {
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
        });
    });
}
