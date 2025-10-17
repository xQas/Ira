## Repo overview

This repository is a small static portfolio website for the artist "Iryna Romanów". It's plain HTML/CSS pages and static assets (images, logo). The site is intended to be hosted as a static site (GitHub Pages is already referenced in README).

Key files/directories
- `index.html`, `aboutme.html`, `portfolio.html`, `contact.html` — main pages. Look here for layout and client-side behavior.
- `styles.css` — single stylesheet that contains layout rules, section sizing and navbar/overlay conventions.
- `data.json` — canonical JSON list of paintings and site metadata; used as a reference for dynamic content if you add scripts.
- `pictures/`, `logo/` — static assets (images). Keep file names and paths exact; HTML references use relative paths like `./pictures/gold.JPG`.

What to expect when making changes
- This is a static site (no build step). Editing HTML/CSS/JS is sufficient; no package.json or server code is present.
- Prefer small, backward-compatible edits to HTML and CSS. Many pages duplicate the same navbar and scripts; changes to behavior should be applied consistently to all pages.

Project-specific conventions and patterns
- Full-page sections: Most pages use <section class="section"> and each section is 100vh in `styles.css`. Preserve `data-bg` attributes ("light" or "dark") when moving or adding sections — JS switches navbar/logo colors based on it (see the inline script at the bottom of pages).
- Logo switching: the navbar logo image is toggled between `./logo/Logo_OG_white.png` and `./logo/Logo_OG.png` depending on the `data-bg` value and viewport width. Update both files when changing logo art.
- Carousels: Bootstrap 5 carousels are used with custom swipe/drag handlers in `portfolio.html`. If updating carousel behavior, update both the markup and the scripts that add touch/mouse handlers.
- Modal images: `portfolio.html` uses a Bootstrap modal with `openModal(imageUrl, title)` to populate the large image; maintain this function signature if you refactor image viewers.
- CSS variables: `styles.css` defines root variables (colors, font). Use them instead of hard-coded colors for consistent theming.

Testing, previewing and common developer workflows
- Preview locally by opening the HTML files directly in a browser (file:///) or by serving the folder with a simple static server. Example quick preview commands (run locally):
  - Python 3: `python3 -m http.server 8000` (serve and open http://localhost:8000)
  - Node (http-server): `npx http-server -c-1 .`
- Browser devtools are the primary debugging tool. Pay attention to console errors for missing image paths; many image references are relative to repository root.

Small implementation notes for AI agents
- When editing shared UI (navbar, scripts), update all pages (`index.html`, `aboutme.html`, `portfolio.html`, `contact.html`) to keep site behavior consistent.
- Use `data.json` as the source of truth if you introduce client-side templates or convert parts of the site to render dynamically. Fields: `paintings[]` with `id`, `title`, `category`, `dimensions`, `price`, `image_url`.
 - Use `data.json` as the canonical source of truth: pages should render dynamically from it on the client-side. Keep `paintings[]` with stable `id` fields and explicit `image_url` keys so a later migration to a backend database is straightforward.
 - Image filenames and paths may change. If you rename or move images, update all HTML references and `data.json` entries. Prefer using stable IDs in `data.json` and an assets mapping (a single place in code that translates painting `id` → filename) to simplify renames and the future DB migration.
- Keep language: pages are in Polish (lang="pl"); maintain that attribute and any visible copy unless asked otherwise.

Examples (concrete patterns)
- Navbar color switch: preserve `data-bg` on section elements. JS reads `section.dataset.bg` and toggles classes `navbar-transparent` / `navbar-scrolled`, swaps `hamburger-white` / `hamburger-black`, and replaces `logo.src`.
- Image modal usage: call `openModal('./pictures/gold.JPG', 'Obraz 1')` — the function sets the modal image element's `src` attribute and updates the modal title element's text.

When to open PRs and testing guidance
- Open small focused PRs (single visual or behavioral change). Include screenshots for UI changes.
- Manual test checklist for UI PRs:
  1. Desktop and mobile viewport check (navbar color/logo, offcanvas menu, carousels).
  2. Verify images load and modal opens.
  3. Confirm no console errors in devtools.

If you need to add automation
- There is no existing test or build framework. If adding tooling, prefer minimal additions (a Tiny dev server script or a single GitHub Action to preview deployment). Document added commands in README.

Owner decisions (answered)
- `data.json` should be used to render pages dynamically client-side. Plan for a future switch to a database by keeping stable `id` fields and an easily-updatable mapping for image assets.
- Image filenames may be changed. When renaming images, update `data.json` and all HTML references. When possible, prefer referencing images through a single mapping layer (ID → filename) to reduce spread-out changes.

If anything here is unclear, ask a specific question (page, file, or feature) and I'll update these instructions.
