#!/usr/bin/env node

import { mkdir, writeFile } from 'node:fs/promises';

const sourceBase = process.env.SOURCE_URL || 'https://catholic-kameari.jp';
const productionBase = process.env.PRODUCTION_URL || 'https://catholic-kameari.jp';
const outDir = new URL('../migration/', import.meta.url);

const categoryNames = new Map();

function stripHtml(value = '') {
  return value
    .replace(/<[^>]*>/g, '')
    .replace(/&amp;/g, '&')
    .replace(/&nbsp;/g, ' ')
    .replace(/&#8211;|&#8212;/g, '-')
    .replace(/&#038;/g, '&')
    .replace(/\s+/g, ' ')
    .trim();
}

function csvEscape(value) {
  const stringValue = String(value ?? '');
  return /[",\n]/.test(stringValue) ? `"${stringValue.replaceAll('"', '""')}"` : stringValue;
}

function toCsv(rows) {
  return rows.map((row) => row.map(csvEscape).join(',')).join('\n') + '\n';
}

function withProductionHost(url) {
  return url.replace(sourceBase.replace(/\/$/, ''), productionBase.replace(/\/$/, ''));
}

async function getJson(path) {
  const response = await fetch(`${sourceBase.replace(/\/$/, '')}${path}`);
  if (!response.ok) {
    throw new Error(`Failed to fetch ${path}: ${response.status} ${response.statusText}`);
  }
  return response.json();
}

async function getAll(path) {
  const url = `${sourceBase.replace(/\/$/, '')}${path}${path.includes('?') ? '&' : '?'}per_page=100`;
  const first = await fetch(url);
  if (!first.ok) {
    throw new Error(`Failed to fetch ${url}: ${first.status} ${first.statusText}`);
  }

  const totalPages = Number(first.headers.get('x-wp-totalpages') || 1);
  const rows = await first.json();

  for (let page = 2; page <= totalPages; page += 1) {
    const response = await fetch(`${url}&page=${page}`);
    if (!response.ok) {
      throw new Error(`Failed to fetch ${url}&page=${page}: ${response.status} ${response.statusText}`);
    }
    rows.push(...await response.json());
  }

  return rows;
}

function pageNote(page) {
  const path = new URL(page.link).pathname;
  if (path === '/') {
    return 'Redesigned homepage. Preserve useful mass, access, news, topic, and introduction content in new front page sections.';
  }

  const stalePages = new Map([
    ['/schedule/annual/', '要確認: 年間予定は年ごとの変更があるため公開前に最新化する。'],
    ['/commit/course/', '要確認: 聖書と典礼の勉強会は「現在中止」とあるため再開状況を確認する。'],
    ['/about/ever/', '要確認: 現在の主任司祭、歴代司祭、修道士情報を公開前に確認する。'],
    ['/access/', '要確認: 駐車場台数、バス停、バリアフリー設備を現地確認する。'],
    ['/schedule/mass/', '要確認: ミサ時刻、英語ミサ、初金、感染症対策文を公開直前に確認する。'],
    ['/memorial/guidance_wedding/', '要確認: 結婚式の受付条件、必要書類、問い合わせ先を確認する。'],
    ['/memorial/memorial/', '要確認: 葬儀、共同墓地、墓参の案内を確認する。'],
  ]);

  return stalePages.get(path) || '移行対象';
}

function postNote(post) {
  const title = stripHtml(post.title.rendered);
  const year = Number(post.date.slice(0, 4));
  const cats = post.categories.map((id) => categoryNames.get(id)?.slug).filter(Boolean);

  if (title.includes('緊急') || title.includes('中止') || title.includes('感染') || title.includes('対応')) {
    return '要確認: 過去の緊急・感染症関連告知。履歴として保持し、最新告知としては扱わない。';
  }
  if (cats.includes('monthly_schedule') || title.includes('予定')) {
    return year < 2026 ? '月間予定アーカイブ。過去予定として保持。' : '要確認: 最新の月間予定として日時を確認。';
  }
  if (cats.includes('topics')) {
    return 'トピックスとして移行。司祭メッセージは著者表記を確認。';
  }
  return '移行対象';
}

const categories = await getAll('/wp-json/wp/v2/categories?_fields=id,slug,name,count');
for (const category of categories) {
  categoryNames.set(category.id, category);
}

const pages = await getAll('/wp-json/wp/v2/pages?_fields=id,date,modified,slug,parent,link,title');
const posts = await getAll('/wp-json/wp/v2/posts?_fields=id,date,modified,slug,link,title,categories');

const inventoryRows = [[
  'type',
  'old_id',
  'title',
  'old_url',
  'planned_new_url',
  'date',
  'modified',
  'categories',
  'migration_status',
  'notes',
]];

for (const page of pages.sort((a, b) => a.link.localeCompare(b.link))) {
  inventoryRows.push([
    'page',
    page.id,
    stripHtml(page.title.rendered),
    page.link,
    withProductionHost(page.link),
    page.date,
    page.modified,
    '',
    'preserve-url',
    pageNote(page),
  ]);
}

for (const post of posts.sort((a, b) => new Date(b.date) - new Date(a.date))) {
  const cats = post.categories
    .map((id) => categoryNames.get(id))
    .filter(Boolean)
    .map((category) => category.slug)
    .join('|');

  inventoryRows.push([
    'post',
    post.id,
    stripHtml(post.title.rendered),
    post.link,
    withProductionHost(post.link),
    post.date,
    post.modified,
    cats,
    'preserve-url',
    postNote(post),
  ]);
}

const redirectRows = [[
  'source_url',
  'target_url',
  'http_status',
  'reason',
]];

for (const page of pages) {
  redirectRows.push([new URL(page.link).pathname, new URL(withProductionHost(page.link)).pathname, '200', 'URL preserved']);
}

for (const post of posts) {
  redirectRows.push([new URL(post.link).pathname, new URL(withProductionHost(post.link)).pathname, '200', 'URL preserved']);
}

redirectRows.push(
  ['/category/news/', '/news/', '301', 'Friendly announcements landing page'],
  ['/category/topics/', '/topics/', '301', 'Friendly topics landing page'],
  ['/category/monthly_schedule/', '/schedule/monthly/', '301', 'Friendly monthly schedule landing page'],
  ['/category/liturgy/', '/schedule/annual/', '301', 'Small liturgy category consolidated into schedules'],
  ['/category/event/', '/schedule/annual/', '301', 'Small event category consolidated into schedules'],
);

const redirectionPluginRows = [['source', 'target', 'regex', 'code']];
for (const row of redirectRows.slice(1).filter((row) => row[2] === '301')) {
  redirectionPluginRows.push([row[0], row[1], '0', row[2]]);
}

const confirmationNotes = `# Manual Confirmation Notes

Generated from ${sourceBase} on ${new Date().toISOString()}.

## Must Confirm Before Launch

- ミサ時刻: /schedule/mass/ was last modified on ${pages.find((page) => new URL(page.link).pathname === '/schedule/mass/')?.modified || 'unknown'}.
- 年間予定: /schedule/annual/ contains recurring annual events and movable liturgical dates. Confirm the current year schedule.
- 月間予定: latest public monthly schedule post is "${stripHtml(posts[0]?.title?.rendered || '')}" dated ${posts[0]?.date || 'unknown'}.
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
`;

const duplicateNotes = `# Duplicate And Outdated Content Notes

## Duplicates Or Consolidation Candidates

- The current "お知らせ" category includes monthly schedules, liturgy notices, event notices, and emergency notices. Keep the archive, but expose friendly landing pages for お知らせ, 月間予定, and トピックス.
- /schedule/ links to the monthly schedule anchor while the menu also links to /category/news/. Replace the public navigation with /schedule/monthly/ and redirect old category archives.
- /memorial/memorial/ covers both funerals and cemetery. Keep the old URL, but present the new menu label as "葬儀・共同墓地".
- Several priest/topic posts use similar greetings and titles. Preserve them as dated posts, but avoid featuring old messages as current parish guidance.

## Outdated Or Uncertain Content

- COVID/infection-prevention notices and mass suspension posts are historical unless parish staff confirms they remain current.
- The annual schedule page was originally published in 2021 and contains movable feast dates. It must be reviewed every year.
- Course information includes "現在中止しています" for 聖書と典礼の勉強会. Confirm before launch.
- Wedding, funeral, cemetery, and access pages should be verified with parish office because they affect visitor decisions.
`;

await mkdir(outDir, { recursive: true });
await writeFile(new URL('content-inventory.csv', outDir), toCsv(inventoryRows));
await writeFile(new URL('redirect-map.csv', outDir), toCsv(redirectRows));
await writeFile(new URL('redirection-plugin-import.csv', outDir), toCsv(redirectionPluginRows));
await writeFile(new URL('manual-confirmation-notes.md', outDir), confirmationNotes);
await writeFile(new URL('duplicate-outdated-notes.md', outDir), duplicateNotes);

console.log(`Wrote ${inventoryRows.length - 1} inventory rows and ${redirectRows.length - 1} redirect rows.`);
