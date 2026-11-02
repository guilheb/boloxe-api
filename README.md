# boloxe-api

Various API for boloxe.com, hosted on Cloudflare Workers.

| Route         | Description                                                                                         |
|---------------|-----------------------------------------------------------------------------------------------------|
| `/photos`     | Flickr portfolio albums, cached in Workers KV by the cron trigger (schedule in `wrangler.jsonc`)    |
| `/calendrier` | Calendar list, or `?id=<id>` for one calendar's ICS feed. Requires the `X-Internal-Api-Key` header  |

## Configuration

Secrets are read from `.env` by `npm run dev`, and set on Cloudflare in the dashboard (Settings → Variables and
Secrets) or with `npx wrangler secret bulk .env`:

- `INTERNAL_API_KEY`
- `FLICKR_API_KEY`, `FLICKR_API_SECRET`, `FLICKR_OAUTH_ACCESS_TOKEN`, `FLICKR_OAUTH_ACCESS_TOKEN_SECRET`, `FLICKR_USER_NSID`
- `CAL_<ID>_URL` for each calendar, with optional `CAL_<ID>_NAME` and `CAL_<ID>_COLOR`

## Development

```sh
npm install
npm run dev        # http://localhost:8787, cron: curl http://localhost:8787/cdn-cgi/handler/scheduled
npm run typecheck
```

Wrangler isn't a dependency: `npx` runs its latest version. `worker-configuration.d.ts` (Workers runtime types, for the
`compatibility_date` in `wrangler.jsonc`) isn't committed: `npm run typecheck` or `npm run types` generates it.

## Deployment

Cloudflare Workers Builds deploys every push to the `production` branch (Git integration set up in the
Cloudflare dashboard). `npm run deploy` deploys from the command line.
