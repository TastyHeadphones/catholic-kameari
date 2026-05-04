# Migration Plan

## Source

Current site: https://catholic-kameari.jp/

The current site is WordPress using Lightning Theme, VK All in One Expansion Unit, and VK Blocks. The public API currently exposes:

- 19 fixed pages.
- 88 posts.
- Categories: お知らせ, トピックス, 典礼行事, 教会行事, 月間予定.

## Migration Inventory

Use `migration/content-inventory.csv` as the authoritative working inventory. It includes:

- Content type.
- Current WordPress ID.
- Title.
- Old URL.
- Planned new URL.
- Published and modified dates.
- Categories.
- Migration status.
- Notes.

## Preferred Migration Procedure

1. Take a full backup of the current production site.
2. Export all WordPress content from the current admin using Tools > Export.
3. Export or download `/wp-content/uploads/`.
4. Start this Docker staging environment.
5. Run `./scripts/bootstrap-wordpress.sh`.
6. Import the WXR file into staging.
7. Copy media uploads into `wp-content/uploads/` or import through WordPress.
8. Confirm the permalink structure is `/%year%/%postname%/`.
9. Import `migration/redirection-plugin-import.csv` into the Redirection plugin.
10. Review every item marked `要確認` in `migration/manual-confirmation-notes.md`.
11. Replace old homepage layout with the new editable homepage pattern.
12. Run visual, mobile, accessibility, and link QA before launch.

## Fallback Migration Procedure

If the current admin export is unavailable:

```bash
./scripts/import-current-site.sh
```

This imports visible rendered HTML from the public WordPress REST API. It preserves meaning, titles, slugs, dates, and categories, but does not preserve original block source, all media metadata, or authors as cleanly as a WXR export.

## Content Rules

- Preserve Japanese as the primary language.
- Preserve useful parish information.
- Mark uncertain information as `要確認` rather than deleting it.
- Preserve old post dates.
- Keep past schedules and emergency notices as archive content.
- Consolidate duplicate navigation labels, but keep original archive URLs or redirects.
- Do not copy text, images, or brand assets from the St. Louis Waco reference site.

## Launch Checklist

- Confirm Mass times.
- Confirm English Mass wording.
- Confirm first Friday Mass and Eucharistic adoration times.
- Confirm annual schedule and movable feast information.
- Confirm course status.
- Confirm priest and friar information.
- Confirm wedding, funeral, and cemetery procedures.
- Confirm access, parking, and bus information.
- Confirm contact phone number.
- Confirm privacy policy.
- Import redirects.
- Submit sitemap in Google Search Console.
- Verify backups can be restored.

