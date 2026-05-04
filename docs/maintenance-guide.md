# Maintenance Guide

## Staff Publishing Workflow

### Add An Announcement

1. WordPress admin > Posts > Add New.
2. Enter the Japanese title.
3. Add the announcement body.
4. Select category `お知らせ`.
5. Add category `月間予定` only when the post is a monthly schedule.
6. Add a featured image when useful.
7. Preview on mobile.
8. Publish.

### Add A Monthly Schedule

Recommended simple workflow:

1. Posts > Add New.
2. Title format: `2026年6月の予定`.
3. Select categories `お知らせ` and `月間予定`.
4. Add the schedule as a clear table or list.
5. Upload and link the PDF if one exists.
6. Publish.

Alternative future workflow: use The Events Calendar for individual events if the parish wants calendar views and recurring event logic.

The `/news/`, `/topics/`, and `/schedule/monthly/` pages use the small `[kameari_posts]` shortcode from the child theme to list posts by category slug. Staff should not need to edit this shortcode unless the category slug changes.

### Edit Mass Times

1. Pages > `ミサのご案内`.
2. Update the schedule table.
3. Update the homepage Mass cards if the time also appears there.
4. Confirm English Mass and first Friday details.
5. Preview and update.

### Edit Access Information

1. Pages > `アクセス`.
2. Update address, parking, barrier-free notes, bus stops, and map.
3. Update the homepage access section if the same information changed.

### Update Priest Message Or Introduction

Use posts for dated priest messages and `/about/ever/` for priest/friar introduction information.

## Update Schedule

- Weekly: publish announcements and schedule updates.
- Monthly: update the monthly schedule and check the homepage latest posts.
- Quarterly: test forms, backups, and security scans.
- Monthly after backup: update WordPress core, theme, and plugins on staging first.

## Editor Guidelines

- Keep paragraphs short and readable.
- Use headings in order.
- Use tables only for real schedule data.
- Add alt text to images.
- Avoid using text inside images for important announcements.
- Mark uncertain older content as `要確認`.
