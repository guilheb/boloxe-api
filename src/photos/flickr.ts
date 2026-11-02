const API_ENDPOINT = 'https://api.flickr.com/services/rest';

const HTTP_TIMEOUT = 30_000;

export interface FlickrCredentials {
    consumerKey: string;
    consumerSecret: string;
    accessToken: string;
    accessTokenSecret: string;
}

/**
 * Flickr API calls signed with OAuth 1.0a (HMAC-SHA1), using an access token obtained beforehand.
 */
export class Flickr {
    constructor(private readonly credentials: FlickrCredentials) {}

    /**
     * Calls a Flickr API method. The response's stat (ok / fail) is left to the caller.
     */
    async call(method: string, parameters: Record<string, string> = {}): Promise<Record<string, any>> {
        const requestParams: Record<string, string> = {
            ...parameters,
            method,
            format: 'json',
            nojsoncallback: '1',
            oauth_nonce: crypto.randomUUID().replaceAll('-', ''),
            oauth_timestamp: String(Math.floor(Date.now() / 1000)),
            oauth_consumer_key: this.credentials.consumerKey,
            oauth_signature_method: 'HMAC-SHA1',
            oauth_version: '1.0',
            oauth_token: this.credentials.accessToken,
        };

        requestParams.oauth_signature = await this.sign('POST', API_ENDPOINT, requestParams);

        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            body: new URLSearchParams(requestParams),
            signal: AbortSignal.timeout(HTTP_TIMEOUT),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    private async sign(method: string, url: string, parameters: Record<string, string>): Promise<string> {
        const joinedParameters = Object.keys(parameters)
            .sort()
            .map(key => `${rawUrlEncode(key)}=${rawUrlEncode(parameters[key]!)}`)
            .join('&');

        const baseString = [method, url, joinedParameters].map(rawUrlEncode).join('&');
        const key = `${rawUrlEncode(this.credentials.consumerSecret)}&${rawUrlEncode(this.credentials.accessTokenSecret)}`;

        const encoder = new TextEncoder();
        const cryptoKey = await crypto.subtle.importKey(
            'raw',
            encoder.encode(key),
            { name: 'HMAC', hash: 'SHA-1' },
            false,
            ['sign'],
        );
        const signature = await crypto.subtle.sign('HMAC', cryptoKey, encoder.encode(baseString));

        return btoa(String.fromCharCode(...new Uint8Array(signature)));
    }
}

// RFC 3986 encoding, as required by OAuth 1.0a (encodeURIComponent leaves !'()* alone)
function rawUrlEncode(value: string): string {
    return encodeURIComponent(value).replace(/[!'()*]/g, char => `%${char.charCodeAt(0).toString(16).toUpperCase()}`);
}
