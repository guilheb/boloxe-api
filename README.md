# boloxe-api

Various API for boloxe.com, hosted on Cloudflare Workers.

| Route         | Description                                                                                         |
|---------------|-----------------------------------------------------------------------------------------------------|
| `/photos`     | Flickr portfolio albums, cached in Workers KV by the cron trigger (schedule in `wrangler.jsonc`)    |
| `/calendrier` | Calendar list, or `?id=<id>` for one calendar's ICS feed. Requires the `X-Internal-Api-Key` header  |

## Configuration

Variables are read from `.env` by `npx wrangler dev`, and managed on Cloudflare in the dashboard (Settings → Variables
and Secrets). Deploys keep them (`keep_vars` in `wrangler.jsonc`).

| Variable                                                                               | Type     |
|----------------------------------------------------------------------------------------|----------|
| `INTERNAL_API_KEY`                                                                     | Secret   |
| `FLICKR_API_SECRET`, `FLICKR_OAUTH_ACCESS_TOKEN`, `FLICKR_OAUTH_ACCESS_TOKEN_SECRET`   | Secret   |
| `FLICKR_API_KEY`, `FLICKR_USER_NSID`                                                   | Text     |
| `CAL_<ID>_URL`, one per calendar (private feed URL)                                    | Secret   |
| `CAL_<ID>_NAME`, `CAL_<ID>_COLOR` (optional)                                           | Text     |

## Development

```sh
npm install
npx wrangler dev --test-scheduled   # http://localhost:8787, cron: curl http://localhost:8787/cdn-cgi/handler/scheduled
npm run typecheck
```

Wrangler isn't a dependency: `npx` runs its latest version. `worker-configuration.d.ts` (Workers runtime types, for the
`compatibility_date` in `wrangler.jsonc`) isn't committed: `npm run typecheck` generates it.

## Deployment

Cloudflare Workers Builds deploys every push to the `production` branch (Git integration set up in the
Cloudflare dashboard). `npx wrangler deploy` deploys from the command line.
