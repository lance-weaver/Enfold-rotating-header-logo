# Enfold Rotating Header Logo

A small WordPress plugin by **Lance Weaver** that randomly swaps the header logo on the [Enfold theme](https://kriesi.at/themes/enfold/) between a set of 2–5 images on every page load.

Built as a standalone plugin (not a child theme) so it survives Enfold theme updates untouched.

Repo: https://github.com/lance-weaver/Enfold-rotating-header-logo (public)

## What it does

- Adds a **Settings → Rotating Logo** admin page where you pick 2–5 images from the WordPress Media Library.
- Adds a **Settings** link directly on the Plugins list page next to Activate/Deactivate.
- On the front end, a small script randomly selects one of those images and swaps it into Enfold's header logo element (`.logo.avia-standard-logo img`) after the page loads.
- The swap happens **client-side, in the visitor's browser** — not on the server. This means it works correctly even if the site uses full-page caching (e.g. a caching plugin, or a host's built-in cache like Hostinger's LiteSpeed cache): every visitor gets a fresh random pick regardless of whether the underlying HTML was served from cache.
- Responsive image attributes (`srcset`, `sizes`, `width`, `height`, `alt`) are generated from each image's real WordPress attachment metadata, so retina/responsive behavior is preserved for whichever logo is chosen.
- **Fallback behavior:** if fewer than 2 logos are selected, or if anything goes wrong while building the logo list, the plugin does nothing at all — Enfold's own normally-configured logo (from Enfold's Theme Options) is left completely untouched. The settings page shows a clear "Active" / "Inactive" status line, and if an error occurred it's shown at the top of the settings page with a note to fix it in the GitHub repo and re-upload, plus a "Dismiss" button.

### Known tradeoff

Because the swap happens after the page loads, there's a brief flash of the site's default/configured Enfold logo before the script replaces it. This is normally not very noticeable, but it's an inherent tradeoff of doing the swap client-side (which is what makes it cache-proof).

## Requirements

- WordPress with the [Enfold theme](https://kriesi.at/themes/enfold/) active (targets Enfold's standard logo markup — `<span class="logo avia-standard-logo">`).
- At least 2 images uploaded (or uploadable) to the WordPress Media Library.

## Installation

1. Download this repository as a ZIP:
   - On GitHub, click **Code → Download ZIP**, or
   - Clone it and zip the folder yourself: the ZIP must contain the plugin files at its root (`enfold-rotating-header-logo.php`, `assets/`, etc.) — if GitHub's "Download ZIP" wraps everything in an extra top-level folder, re-zip so `enfold-rotating-header-logo.php` is at the top level of the archive, or just upload the extra-wrapped zip — WordPress handles either.
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate**.
5. Go to **Settings → Rotating Logo** (or click **Settings** on the plugin's row on the Plugins page).
6. Use **Select Image** on at least 2 of the 5 slots to pick logo images from your Media Library.
7. Click **Save Changes**.

That's it — reload the front end of your site a few times (or open in a private/incognito window to avoid browser caching of the page itself) to see the logo change.

## Updating the logo set later

Go back to **Settings → Rotating Logo** at any time, change any of the 5 slots (use **Remove** to clear a slot), and save. No code editing required.

## Updating the plugin code itself

Two ways to get code changes from this repo onto your live site, in order of convenience:

### Option A: Automatic, via the "Git Updater" plugin (recommended)

1. Install the free [Git Updater](https://github.com/afragen/git-updater) plugin on your WordPress site (download its ZIP from that repo and install the same way as any other plugin, or see its own install docs).
2. Nothing else to configure — this plugin's header already includes:
   ```
   GitHub Plugin URI: lance-weaver/Enfold-rotating-header-logo
   GitHub Branch: master
   ```
   Git Updater reads this automatically since the repo is public (no token needed).
3. To ship a new version: bump the `Version:` number in `enfold-rotating-header-logo.php`, commit, push to `master`, then create a matching GitHub Release/tag (e.g. `v1.2`) — either via the GitHub website ("Releases" → "Draft a new release") or the CLI: `git tag v1.2 && git push --tags && gh release create v1.2 --generate-notes`.
4. WordPress's normal Plugins page will then show the standard "update available" notice, and clicking **Update Now** pulls the new release from GitHub automatically.

### Option B: Manual re-upload (always works, no extra plugin)

1. Make your changes, commit and push to GitHub as usual.
2. Download the repo as a ZIP (Code → Download ZIP, or package it yourself as described in Installation above).
3. WordPress admin → Plugins → Add New → Upload Plugin → choose the ZIP.
4. WordPress will detect the plugin is already installed and offer **"Replace current with uploaded"** — confirm it.
5. This replaces only the plugin's files. All settings (selected logos, etc.) live in the WordPress database (`wp_options` table), completely separate from the plugin files, so nothing is lost by replacing the code this way.

## Uninstalling

Deactivating or deleting the plugin removes the random-swap behavior immediately; Enfold's normally configured logo (set in Enfold's own Theme Options) will display as usual. The plugin does not modify any Enfold theme settings.

## License

GPLv2 or later, consistent with the WordPress plugin ecosystem.
