# Frontend Deployment Notes

The SPA is built with Vite and expects the backend URL to be provided through the `VITE_API_URL` environment variable. Use `.env.production.local` (ignored by Git) or provide a file referenced by `FRONTEND_ENV_FILE` in `deployment/cpanel/deploy.sh` when building on the server.

Typical production values when the backend lives at `https://api.alawalapp.com`:

```bash
VITE_API_URL=https://api.alawalapp.com/api
```

Run the production build locally with:

```bash
npm ci
npm run build
serve -s dist
```

On cPanel the automatic deployment script handles installing dependencies, running `npm run build`, and publishing the `dist/` folder to the configured document root.
