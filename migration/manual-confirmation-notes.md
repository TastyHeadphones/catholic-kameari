# Manual Confirmation Notes

Generated from https://catholic-kameari.jp on 2026-05-04T23:04:47.093Z.

## Must Confirm Before Launch

- ミサ時刻: /schedule/mass/ was last modified on 2026-04-29T20:06:29.
- 年間予定: /schedule/annual/ contains recurring annual events and movable liturgical dates. Confirm the current year schedule.
- 月間予定: latest public monthly schedule post is "2026年5月の予定" dated 2026-04-29T20:09:50.
- 講座・勉強会: current page states that 聖書と典礼の勉強会 is currently suspended after a priest transfer. Confirm whether it should remain suspended.
- 司祭紹介: confirm current主任司祭 and any friar/priest names before publication.
- Access: confirm parking count, bus access, accessible toilet, and ramp wording.
- Weddings, funerals, and cemetery: confirm current procedure, eligibility, fees, and contact route.
- Infection-prevention text: retain if still parish policy; otherwise move to an archived notice.

## Preserve As Historical Archive

- Past monthly schedule posts from 2021-2025.
- Emergency, suspension, and COVID-era notices.
- Past priest messages and topics posts.

## Migration Method

Preferred: export a WordPress WXR file from the current admin and import it into staging so original block markup, attachments, authors, dates, and IDs are preserved as much as possible.

Fallback: run scripts/import-current-site.sh to import public REST API rendered HTML. This preserves visible content and dates, but not original block editor source or all media metadata.
