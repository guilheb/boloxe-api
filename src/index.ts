import type { Env } from './env';
import { calendrier } from './endpoints/calendrier';
import { photos, PHOTOS_CACHE_KEY } from './endpoints/photos';
import { Photos } from './photos/photos';

export default {
    async fetch(request, env): Promise<Response> {
        const path = new URL(request.url).pathname.replace(/\/+$/, '');

        switch (path) {
            case '/photos':
                return photos(request, env);
            case '/calendrier':
                return calendrier(request, env);
            default:
                return new Response(null, { status: 404 });
        }
    },

    // Cron trigger (wrangler.jsonc): refreshes the albums served by /photos
    async scheduled(_controller, env): Promise<void> {
        const albums = await new Photos(env).fetch();

        await env.CACHE.put(PHOTOS_CACHE_KEY, JSON.stringify(albums));
    },
} satisfies ExportedHandler<Env>;
