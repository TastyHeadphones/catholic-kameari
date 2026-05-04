# Information Architecture

The new structure preserves current useful content while making Mass, access, announcements, and visitor guidance easier to find.

## Primary Navigation

- ミサ
  - `/schedule/mass/`
  - Purpose: Mass times, first Friday, English Mass, participation notes.
- 初めての方へ
  - `/about/visitors/`
  - Purpose: welcoming visitor guidance and Mass etiquette.
- 教会紹介
  - `/about/`
  - `/about/introduction/`
  - `/about/history/`
  - `/about/ever/`
- お知らせ
  - `/news/`
  - Current archive: `/category/news/`
- トピックス
  - `/topics/`
  - Current archive: `/category/topics/`
- 教会活動
  - `/commit/`
  - `/commit/family/`
  - `/commit/commit/`
  - `/commit/course/`
- 結婚式・葬儀
  - `/memorial/`
  - `/memorial/guidance_wedding/`
  - `/memorial/memorial/`
- アクセス
  - `/access/`

## Required Sections Covered

- Home: `/`
- About the Church: `/about/`, `/about/introduction/`
- First-time Visitors: `/about/visitors/`
- Church History: `/about/history/`
- Mass Schedule: `/schedule/mass/`
- Monthly Schedule: `/schedule/monthly/` and current schedule posts
- Annual Events: `/schedule/annual/`
- Announcements / News: `/news/`, `/category/news/`
- Topics: `/topics/`, `/category/topics/`
- Church Activities: `/commit/`, `/commit/commit/`
- Courses / Bible Study: `/commit/course/`
- Weddings: `/memorial/guidance_wedding/`
- Funerals: `/memorial/memorial/`
- Cemetery: `/memorial/memorial/`
- Priest Introduction: `/about/ever/`
- Access: `/access/`
- Contact: `/contact/`
- Privacy Policy: `/privacy-policy/`

## Homepage Hierarchy

1. Header with church name, Japanese-first navigation, and Mass/Access CTAs.
2. Hero with church photography, Catholic Kameari Church title, St. Francis of Assisi subtitle, welcome message, and buttons.
3. Quick Mass information cards.
4. Latest announcements.
5. First-time visitor section.
6. Parish introduction and history.
7. Parish life cards.
8. Schedule preview.
9. Access with address, transit notes, map, and Google Maps button.
10. Footer with parish details, navigation, social links, copyright, and Archdiocese link.

## URL Policy

Preserve current page and post URLs where practical. The main post permalink structure remains `/%year%/%postname%/`, so the existing dated Japanese post URLs can continue to work.

Friendly landing pages are added for `/news/`, `/topics/`, and `/schedule/monthly/`. Old category archive URLs are redirected to those landing pages in `migration/redirection-plugin-import.csv`.

