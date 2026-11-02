export interface Env {
    CACHE: KVNamespace;

    // Refused as long as it isn't set
    INTERNAL_API_KEY?: string;

    FLICKR_API_KEY: string;
    FLICKR_API_SECRET: string;
    FLICKR_OAUTH_ACCESS_TOKEN: string;
    FLICKR_OAUTH_ACCESS_TOKEN_SECRET: string;
    FLICKR_USER_NSID: string;

    // CAL_<ID>_URL, CAL_<ID>_NAME, CAL_<ID>_COLOR
    [name: string]: unknown;
}
