# Changelog — Yandex Webmaster meta tag

**Date:** 2026-07-15
**Task:** LAUNCH TASK — Production Readiness
**Site:** https://laser178.ru

## Changes

- Added Yandex Webmaster verification meta tag to site `<head>`.
  - Meta tag: `<meta name="yandex-verification" content="d14b5b5c9bd82699" />`
  - Method: `wp_head` hook in `laser178-site-plugin.php`.
- Added Google Search Console verification meta tag to site `<head>`.
  - Meta tag: `<meta name="google-site-verification" content="oy7Re28KhzfEXISiY70gWPe2rR6CboRnENtSTN-InAE" />`
  - Method: `wp_head` hook in `laser178-site-plugin.php`.

## Files

- `laser178-site-plugin.php` — added `l178_meta_verifications()` function with both verification tags.
- `laser178-site-plugin_BACKUP.php` — original file before modification.

## Verification

Command used:
```bash
curl -s -A "Mozilla/5.0" "https://laser178.ru/" | grep -iE "yandex-verification|google-site-verification"
```

Result:
```html
<meta name="yandex-verification" content="d14b5b5c9bd82699" />
<meta name="google-site-verification" content="oy7Re28KhzfEXISiY70gWPe2rR6CboRnENtSTN-InAE" />
```

## Next steps

- Verify ownership in Yandex Webmaster dashboard.
- Verify ownership in Google Search Console dashboard.
- Submit sitemap to both services.
