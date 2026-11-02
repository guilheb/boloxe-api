// boloxe.com and its subdomains, plus localhost for development
const ALLOWED_ORIGINS = /^https?:\/\/(([\w-]+\.)*localhost|([\w-]+\.)*boloxe\.com)(:\d+)?$/;

const PREFLIGHT_MAX_AGE = 86400;

/**
 * Adds the CORS headers for an allowed Origin to the handler's response and refuses the others with a 403.
 * A preflight request (OPTIONS) is answered right away, without calling the handler.
 *
 * @param allowedHeaders Request headers the browser may send, besides the simple ones
 */
export async function withCors(
    request: Request,
    handler: () => Promise<Response>,
    allowedHeaders: string[] = [],
): Promise<Response> {
    const headers = new Headers({ Vary: 'Origin' });
    const origin = request.headers.get('Origin');

    if (origin !== null) {
        if (!ALLOWED_ORIGINS.test(origin)) {
            return new Response(null, { status: 403, headers });
        }

        headers.set('Access-Control-Allow-Origin', origin);

        if (request.method === 'OPTIONS') {
            headers.set('Access-Control-Allow-Methods', 'GET');
            if (allowedHeaders.length > 0) {
                headers.set('Access-Control-Allow-Headers', allowedHeaders.join(', '));
            }
            headers.set('Access-Control-Max-Age', String(PREFLIGHT_MAX_AGE));
            return new Response(null, { status: 204, headers });
        }
    }

    const response = await handler();

    for (const [name, value] of headers) {
        response.headers.set(name, value);
    }

    return response;
}
