# Kameari Church — WordPress theme guide

`kameari-church` is a **standalone classic WordPress theme**. It needs no parent
theme, no page builder, and no plugins. It is a direct port of the Claude Design
project *kameari-church* (bone-and-ink surfaces, one sumi-green accent, Shippori
Mincho B1 display type, JetBrains Mono micro-labels).

- Source: `wp-content/themes/kameari-church/`
- Release asset: `kameari-church.zip` on the repository's Releases page
- Requires: WordPress 6.5+, PHP 8.0+

---

## 1. Install

### From the release zip (recommended for a live site)

1. Download `kameari-church.zip` from
   [Releases](https://github.com/TastyHeadphones/catholic-kameari/releases).
2. **wp-admin → Appearance → Themes → Add New → Upload Theme**.
3. Choose the zip, install, then **Activate**.

### From this repository (Docker / Railway)

The theme lives at `wp-content/themes/kameari-church/`, which is already part of
the image build context. Rebuild the image and the theme is present; activate it
once from wp-admin, or with WP-CLI:

```bash
wp theme activate kameari-church
```

### Updating later

Upload the new zip the same way and choose **Replace current with uploaded**.
Your settings survive: they are stored as WordPress *theme mods* keyed to the
theme folder name, which does not change between versions.

---

## 2. First-run checklist

Work through this in order — the front page stays empty until steps 2 and 3 are
done.

1. **Appearance → Themes** — activate *Kameari Church*.
2. **Settings → Reading** — set *Your homepage displays* to **A static page**,
   choose a front page (e.g. ホーム) and a posts page (e.g. お知らせ).
3. **Mass Times** — add one entry per Mass. See §4.
4. **Appearance → Menus** — create menus and assign them to the five locations
   (§5).
5. **Appearance → Customize** — set the hero, the address, and the footer (§6).
6. **Liturgical Calendar / Parish Activities / Clergy** — optional but they
   switch off entire front-page strips when left empty.

---

## 3. What the front page is made of

The front page is assembled top to bottom from these strips. Each one can be
switched off in **Customize → Front page — sections**, and each one also hides
itself automatically when it has no content.

| # | Strip | Content comes from |
|---|---|---|
| — | Hero | Customize → Front page — hero |
| — | (optional prose) | The static front page's own block content |
| 02 | Mass times (ink panel) | **Mass Times** post type |
| 03 | News (5-card grid) | Your five most recent **Posts** |
| 04 | Liturgical calendar | **Liturgical Calendar** post type |
| — | Pastor's welcome (split) | Customize → Front page — pastor's welcome |
| 05 | Parish activities | **Parish Activities** post type |
| — | Verse of the day | Customize → Front page — scripture |
| — | Visitor guide | Customize → Front page — visitor guide |
| — | Access strip | Customize → Contact & access |

If the page you set as the static front page has block content of its own, it is
rendered as a prose section directly beneath the hero. Leave it empty if you
only want the designed strips.

---

## 4. The four parish post types

The theme registers four admin-only post types. They have no public URLs — they
exist to fill rows in the design.

### Mass Times (`ミサ時間`)

| Field | Example | Notes |
|---|---|---|
| Title | 主日のミサ（集会） | The name of the Mass |
| Day (Japanese) | 日曜日 / 月・水・金 | Left column |
| Day (Latin letters) | SUN / MON · WED · FRI | Small line under it |
| Time | 10:00 | Rendered large, in gold |
| Note | 日曜学校・聖歌隊 | Small line under the name |
| Highlight this row | ✔ | Use for **one** row — the principal Sunday Mass |

### Liturgical Calendar (`典礼暦`)

Title is the feast name. *Date label* (`5月10日`) and *Weekday* (`日`) are what
readers see; *Sort date* is optional but recommended — **rows whose sort date is
in the past are hidden from the front page**, so the calendar shrinks by itself
instead of showing stale feasts. Up to eight upcoming rows are shown.

### Parish Activities (`教会活動`)

Title plus the **excerpt** (the tile's description) plus an optional **Link**.
A tile with no link is not clickable. The front page shows the first six; the
`NN / TT` counter uses the total number of published activities.

### Clergy (`司祭`)

Name, role, Latin-letter name, years in post, and a featured image. With no
featured image the theme draws a minimal portrait; the *Portrait tone* field
picks a dark or light one.

### Ordering

All four use **Order** (Page Attributes). Lower numbers come first; ties fall
back to the creation date. Set it explicitly — the Mass list in particular reads
badly in creation order.

---

## 5. Menus

**Appearance → Menus.** Five locations:

| Location | Where it appears |
|---|---|
| Primary navigation | Desktop header |
| Mobile navigation | The drawer — falls back to Primary if unset |
| Footer — Parish | First footer column |
| Footer — Worship | Second footer column |
| Footer — Contact | Third footer column |

A footer column is omitted entirely when its menu is unassigned. The header
supports **one** level of dropdown; deeper levels are not rendered.

The dark button at the right of the header is not a menu item — it is the
visitor-guide call to action, and it appears only when **both**
*Customize → Front page — visitor guide → Button link* and *Button label* are
filled in.

---

## 6. Customizer settings

| Panel | What it controls |
|---|---|
| Church identity | Church name, Latin subtitle, accent colour (sumi green / ink navy / walnut), Google Fonts on/off |
| Front page — hero | Photograph, kicker, headline, standfirst, two buttons, the "next service" block |
| Front page — scripture | The quotation beside the Mass panel, and the verse of the day |
| Contact & access | Postal code, address, phone, hours, email, nearest station, parking, map embed |
| Footer | Tagline and motto |
| Front page — sections | Show/hide each strip |
| Front page — pastor's welcome | Photograph, heading, body, pull quote, signature |
| Front page — visitor guide | Heading, body, button, and the five steps |

Two fields have a specific format:

- **Headlines and quotations** — one line break per rendered line. The design's
  headlines are hand-broken; that is deliberate, not a bug.
- **Five steps** — one step per line, written as `headline | explanation`.
  The `NN` numbering is generated.

---

## 7. Writing pages and posts

The theme ships **theme.json** and editor styles, so the block editor looks like
the front end. Two page templates and four block patterns are available.

### Page templates

- **Default** — title, optional standfirst, prose column.
- **Page with side index** — the two-column layout from the design; the left
  index lists the page's child pages, or its siblings if it has none.
- **Wide page (blocks edge to edge)** — no prose container, for pages built out
  of full-width patterns.

The **page excerpt** becomes the standfirst — the calm lead paragraph under the
title. Set one on every page; without it the title sits alone.

### Block patterns (Kameari Church category)

Callout note · Mass schedule table · Numbered guide list · Three sacrament tiles
· Scripture quotation.

### Block styles

Applied from the block sidebar → Styles:

- Group → **Kameari callout**
- Paragraph → **Kameari micro label**
- List → **Kameari numbered rules**
- Table → **Kameari schedule**

### News

Ordinary posts. The **first category** becomes the tag chip; the date is shown
as `YYYY.MM.DD`. Featured images are used where present — otherwise one of the
drawn illustrations is picked, deterministically, from the post ID, so a given
article always keeps the same drawing.

---

## 8. Images

| Slot | Recommended size | Notes |
|---|---|---|
| Hero background | 2200 × 1200 or larger | Darkened to 66% brightness under the headline. Avoid images that are already dark on the left. |
| News featured image | 900 × 675 (4:3) | The second card in the grid is cropped to 3:4 |
| Clergy portrait | 800 × 1000 (4:5) | Optional |
| Custom logo | Square, ~80 × 80 | Replaces the drawn church mark in the header. The footer always uses the drawn mark, in white. |

---

## 9. Things to be careful about

These are the parts that will bite if nobody reads them.

### The post types belong to the theme

`Mass Times`, `Liturgical Calendar`, `Parish Activities` and `Clergy` are
registered **by this theme**. If you switch to another theme, the rows stay in
the database but the admin screens disappear and nothing renders them. This is
normal for a bespoke parish theme, but it means: **do not switch themes casually
on the live site.** If you ever migrate, move these into a small site-specific
plugin first.

### Switching away from the Kadence child theme

This repository also contains `kameari-kadence-child`. The two are unrelated —
activating Kameari Church does not carry over its patterns or its settings.
Customizer values are stored per theme, so switching back and forth is safe
(each theme keeps its own), but the homepage will look completely different the
moment you switch. **Take a backup first** (see `docs/backup-restore.md`).

### Google Fonts is an external request

By default the theme loads Shippori Mincho B1, Noto Sans JP, Inter and JetBrains
Mono from `fonts.googleapis.com`. That is a third-party request from every
visitor's browser, and it costs a round trip on first paint. Turn it off in
**Customize → Church identity** if you would rather self-host the fonts or avoid
the external call — the theme then falls back to Hiragino Mincho / Yu Mincho and
the system sans, which are present on most Japanese devices and still look
right. The layout does not shift either way.

### The map field wants a URL, not an iframe

**Customize → Contact & access → Map embed URL** takes only the `src` value of a
map iframe — the string starting `https://www.google.com/maps/embed?...`. Paste
the whole `<iframe …>` tag and you get an empty box. Leave it blank to keep the
drawn map illustration, which needs no third-party request and no cookie banner.

### Headlines are hand-broken

The hero headline, the section headings and the quotations break where you put
the line breaks — they do not reflow. Check them on a phone after editing. Two
or three short lines is the design's intent; a single long line will overflow on
narrow screens.

### Highlight exactly one Mass

The *Highlight this row* switch enlarges the time and adds a gold wash. Ticking
it on several rows flattens the effect the design depends on.

### Excerpt length is measured in characters

Japanese has no word boundaries, so the theme trims excerpts by character count
(78 characters on the front page, 90 in lists) rather than by words. Write a
manual excerpt when the automatic cut lands mid-phrase.

### Contrast on the ink surfaces

The Mass panel, the verse strip and the visitor block are near-black. Do not
lower the accent colour's darkness or place mid-grey text on them. Activity
tiles invert to ink-on-hover — keep tile copy short so the inverted state stays
legible.

### Customise through a child theme

Edit the theme files directly and the next zip upload overwrites your changes.
For anything beyond the Customizer, make a child theme:

```
wp-content/themes/kameari-church-child/style.css
```

```css
/*
Theme Name: Kameari Church Child
Template: kameari-church
Version: 1.0.0
*/
```

…plus a `functions.php` that enqueues the parent stylesheet.

### What did not come across from the design

The Claude Design prototype is a React single-page app. Its client-side page
router, its live "Tweaks" panel, and its print layout are prototype tooling and
are deliberately not part of the theme — WordPress handles routing, the
Customizer replaces the tweaks panel, and printing uses the browser's own.

---

## 10. Development notes

- No build step. One stylesheet (`style.css`), one 2 KB script
  (`assets/js/navigation.js`), one editor stylesheet.
- All twelve illustrations are generated in PHP as inline SVG
  (`inc/illustrations.php`) and coloured with the same CSS custom properties as
  the rest of the theme, so the accent switch recolours them too.
- Text domain: `kameari-church`. Strings are wrapped for translation; no `.pot`
  is shipped yet.
- CI: `.github/workflows/theme-release.yml` runs `php -l` over every theme file
  on push and pull request, and attaches `kameari-church.zip` to any release
  tagged `theme-v*`.

### Cutting a new release

```bash
git tag theme-v1.0.1 && git push origin theme-v1.0.1
gh release create theme-v1.0.1 --title "Kameari Church 1.0.1" --notes "…"
```

The workflow builds the zip and uploads it to that release.

---

## File map

```
wp-content/themes/kameari-church/
├── style.css                     theme header + the whole design system
├── theme.json                    editor palette, fonts, layout widths
├── functions.php                 setup, assets, excerpts, pagination
├── header.php  footer.php        masthead, drawer, footer columns
├── front-page.php                the designed home
├── index.php  single.php  page.php  search.php  404.php  comments.php
├── searchform.php  readme.txt  screenshot.png
├── inc/
│   ├── customizer.php            every Customizer setting
│   ├── content-types.php         the four parish post types
│   ├── meta-boxes.php            their fields
│   ├── illustrations.php         twelve inline SVG artworks
│   ├── template-tags.php         logo, brand, labels, section heads
│   └── patterns.php              block styles and block patterns
├── page-templates/
│   ├── with-index.php            two-column prose layout
│   └── wide.php                  edge-to-edge blocks
├── template-parts/
│   ├── content-list.php  content-none.php
│   └── home/                     hero, mass, news, liturgy, about,
│                                 activities, verse, visit, access
└── assets/
    ├── css/editor.css
    └── js/navigation.js
```
