# Technical Recommendation

## Recommendation

Use WordPress with Kadence Theme, a small Kadence child theme, Gutenberg, and a minimal set of reputable plugins.

This is the best fit because the current Catholic Kameari Church site is already WordPress, the public REST API exposes the existing content, and parish staff are most likely to be comfortable with pages, posts, media uploads, and a familiar admin screen.

## Selected Stack

- WordPress latest stable Docker image with PHP 8.2.
- MariaDB 10.11 LTS.
- Kadence Theme as the parent theme.
- Catholic Kameari Kadence child theme for design tokens, CSS, schema, and block patterns.
- Gutenberg block editor.
- Kadence Blocks for flexible page editing.
- The Events Calendar for monthly and annual schedules if the parish wants event objects rather than schedule posts.
- Contact Form 7 for simple forms.
- Yoast SEO for titles, meta descriptions, XML sitemap, Open Graph, and basic schema.
- UpdraftPlus for scheduled backups.
- Wordfence for security hardening and alerts.
- LiteSpeed Cache if production hosting supports LiteSpeed, otherwise use the host cache.
- Redirection for old URL redirects.

## Alternatives Considered

### Astra Theme

Astra is also a good option. It is mature, fast, and well supported. Kadence is preferred here because it provides stronger block-based layout control and a polished editor workflow without requiring a page builder.

### Next.js With Markdown Or MDX

This would be fast and technically clean, but it would make announcements, monthly schedules, access updates, and priest messages dependent on a developer or Git-based CMS workflow. That is not ideal for parish staff.

### Headless CMS

Strapi, Directus, Payload, or Decap CMS would add hosting, authentication, backup, and upgrade complexity. The site does not need that complexity.

### Elementor Or A Heavy Page Builder

Avoid unless a future editor specifically requires drag-and-drop layout control. Gutenberg and Kadence Blocks are enough for this project and have lower long-term risk.

## Maintenance Implications

Church staff can update:

- Announcements as posts.
- Monthly schedules as posts or Events Calendar events.
- Mass times as a reusable block or page section.
- Access information as a page.
- Activity, wedding, funeral, cemetery, and course pages as normal WordPress pages.
- Images through the media library.

Developer involvement should be limited to theme updates, major design changes, performance tuning, or complex migrations.

## Hosting Approach

Recommended production hosting:

- Managed WordPress host with staging.
- PHP 8.2 or newer.
- MariaDB/MySQL.
- SSL and automated daily backups.
- Server-level cache.
- WAF or host-level malware scanning where available.

Docker is included for local and staging use. Production can run Docker if the parish has reliable technical support, but managed WordPress hosting is simpler for a local parish.

## Staff Difficulty

Estimated staff difficulty after setup: low to moderate.

Posting announcements and updating schedules should be comparable to the current WordPress workflow. Editing the homepage will be easier if the Mass time and access sections are treated as reusable blocks with clear labels.

