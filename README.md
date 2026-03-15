# pat-weight-log

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Recommended Browser Setup

- Chromium-based browsers (Chrome, Edge, Brave, etc.):
  - [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
  - [Turn on Custom Object Formatter in Chrome DevTools](http://bit.ly/object-formatters)
- Firefox:
  - [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)
  - [Turn on Custom Object Formatter in Firefox DevTools](https://fxdx.dev/firefox-devtools-custom-object-formatters/)

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

## PHP API setup (for FTP hosting)

The app now saves and loads weights through `api/weights.php`, which reads/writes:

- `data/weight-history.json`

Supported API methods:

- `GET /api/weights.php` -> list records
- `POST /api/weights.php` -> create record
- `DELETE /api/weights.php?id=123` -> delete record

### Local development with Vite + PHP

1. Create `.env` from `.env.example` and keep:

```sh
# For local dev:
VITE_API_BASE_URL=http://localhost:8000
VITE_WEIGHT_API_TOKEN=change-me
# For production, leave VITE_API_BASE_URL empty (default):
# VITE_API_BASE_URL=
```

2. Run frontend and API together:

```sh
npm run dev:full
```

- Vite: `http://localhost:5173`
- PHP API: `http://localhost:8000/api/weights.php`

### FTP deployment (shared hosting)

1. Build frontend:

```sh
npm run build
```

2. Upload these folders/files to your hosting root (or `public_html`):

- `dist/*` contents (frontend static files)
- `api/weights.php`
- `data/weight-history.json`

3. Ensure `data/weight-history.json` is writable by PHP on the server.

4. Optional but recommended: define `WEIGHT_API_TOKEN` in your hosting PHP environment. Then build frontend with matching `VITE_WEIGHT_API_TOKEN`.

5. In production, keep `VITE_API_BASE_URL` empty (default) so frontend calls `/api/weights.php` on same domain. All `localhost` URLs are for local development only.

### Lint with [ESLint](https://eslint.org/)

```sh
npm run lint
```
