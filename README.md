# Enfold Rotating Header Logo

A small WordPress plugin that randomly swaps the header logo on the [Enfold theme](https://kriesi.at/themes/enfold/) between a set of 2–5 images on every page load.

Built as a standalone plugin (not a child theme) so it survives Enfold theme updates untouched.

## What it does

- Adds a **Settings → Random Logo** admin page where you pick 2–5 images from the WordPress Media Library.
- On the front end, a small script randomly selects one of those images and swaps it into Enfold's header logo element (`.logo.avia-standard-logo img`) after the page loads.
- The swap happens **client-side, in the visitor's browser** — not on the server. This means it works correctly even if the site uses full-page caching (e.g. a caching plugin, or a host's built-in cache like Hostinger's LiteSpeed cache): every visitor gets a fresh random pick regardless of whether the underlying HTML was served from cache.
- Responsive image attributes (`srcset`, `sizes`, `width`, `height`, `alt`) are generated from each image's real WordPress attachment metadata, so retina/responsive behavior is preserved for whichever logo is chosen.
- If fewer than 2 logos are configured, the plugin does nothing and Enfold's normal configured logo displays as usual.

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
5. Go to **Settings → Random Logo**.
6. Use **Select Image** on at least 2 of the 5 slots to pick logo images from your Media Library.
7. Click **Save Changes**.

That's it — reload the front end of your site a few times (or open in a private/incognito window to avoid browser caching of the page itself) to see the logo change.

## Updating the logo set later

Go back to **Settings → Random Logo** at any time, change any of the 5 slots (use **Remove** to clear a slot), and save. No code editing required.

## Uninstalling

Deactivating or deleting the plugin removes the random-swap behavior immediately; Enfold's normally configured logo (set in Enfold's own Theme Options) will display as usual. The plugin does not modify any Enfold theme settings.

## License

GPLv2 or later, consistent with the WordPress plugin ecosystem.
