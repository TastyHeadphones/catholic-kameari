#!/usr/bin/env node

import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { createWriteStream } from 'node:fs';
import { pipeline } from 'node:stream/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const sourceBase = process.env.SOURCE_URL || 'https://catholic-kameari.jp';
const rootDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const outDir = path.join(rootDir, 'migration', 'source-content');
const rawDir = path.join(outDir, 'raw');
const renderedDir = path.join(outDir, 'rendered-html');
const mediaDir = path.join(outDir, 'media');
const downloadMedia = process.env.DOWNLOAD_MEDIA !== '0';

const endpointDefinitions = [
  ['pages', '/wp-json/wp/v2/pages?_fields=id,date,modified,slug,parent,link,title,excerpt,content,featured_media,template,menu_order'],
  ['posts', '/wp-json/wp/v2/posts?_fields=id,date,modified,slug,link,title,excerpt,content,categories,tags,featured_media,author'],
  ['categories', '/wp-json/wp/v2/categories?_fields=id,count,description,link,name,slug,parent,taxonomy'],
  ['tags', '/wp-json/wp/v2/tags?_fields=id,count,description,link,name,slug,taxonomy'],
  ['media', '/wp-json/wp/v2/media?_fields=id,date,modified,slug,link,title,caption,description,alt_text,media_type,mime_type,source_url,media_details'],
  ['users', '/wp-json/wp/v2/users?_fields=id,name,slug,link,description'],
];

function safeFileName(value) {
  return String(value || 'item')
    .normalize('NFKC')
    .replace(/[\\/:*?"<>|#%{}^~[\]`;\s]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
    .slice(0, 120) || 'item';
}

function stripHtml(value = '') {
  return value
    .replace(/<[^>]*>/g, '')
    .replace(/&amp;/g, '&')
    .replace(/&nbsp;/g, ' ')
    .replace(/&#038;/g, '&')
    .replace(/\s+/g, ' ')
    .trim();
}

async function getJson(url) {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Failed to fetch ${url}: ${response.status} ${response.statusText}`);
  }

  return {
    data: await response.json(),
    totalPages: Number(response.headers.get('x-wp-totalpages') || 1),
    total: Number(response.headers.get('x-wp-total') || 0),
  };
}

async function getAll(endpoint) {
  const joiner = endpoint.includes('?') ? '&' : '?';
  const firstUrl = `${sourceBase.replace(/\/$/, '')}${endpoint}${joiner}per_page=100&page=1`;
  const first = await getJson(firstUrl);
  const rows = Array.isArray(first.data) ? first.data : [];

  for (let page = 2; page <= first.totalPages; page += 1) {
    const pageUrl = `${sourceBase.replace(/\/$/, '')}${endpoint}${joiner}per_page=100&page=${page}`;
    const next = await getJson(pageUrl);
    rows.push(...next.data);
  }

  return {
    rows,
    total: first.total || rows.length,
    totalPages: first.totalPages,
  };
}

async function writeRenderedHtml(type, rows) {
  const typeDir = path.join(renderedDir, type);
  await mkdir(typeDir, { recursive: true });

  for (const row of rows) {
    if (!row.content?.rendered) {
      continue;
    }

    const title = stripHtml(row.title?.rendered || row.slug || row.id);
    const fileName = `${String(row.id).padStart(5, '0')}-${safeFileName(row.slug || title)}.html`;
    const html = [
      `<!-- source: ${row.link || sourceBase} -->`,
      `<!-- title: ${title} -->`,
      `<!-- date: ${row.date || ''} -->`,
      `<!-- modified: ${row.modified || ''} -->`,
      row.content.rendered,
      '',
    ].join('\n');

    await writeFile(path.join(typeDir, fileName), html);
  }
}

async function downloadMediaFiles(mediaRows) {
  const manifest = [];

  for (const item of mediaRows) {
    if (!item.source_url) {
      continue;
    }

    const url = new URL(item.source_url);
    const uploadIndex = url.pathname.indexOf('/wp-content/uploads/');
    const relative = uploadIndex >= 0
      ? url.pathname.slice(uploadIndex + '/wp-content/uploads/'.length)
      : path.basename(url.pathname);
    const localPath = path.join(mediaDir, relative);

    await mkdir(path.dirname(localPath), { recursive: true });

    const response = await fetch(item.source_url);
    if (!response.ok || !response.body) {
      throw new Error(`Failed to download media ${item.source_url}: ${response.status} ${response.statusText}`);
    }

    await pipeline(response.body, createWriteStream(localPath));
    manifest.push({
      id: item.id,
      title: stripHtml(item.title?.rendered || item.slug),
      source_url: item.source_url,
      local_path: path.relative(outDir, localPath),
      mime_type: item.mime_type,
      media_type: item.media_type,
    });
  }

  await writeFile(path.join(outDir, 'media-files.json'), JSON.stringify(manifest, null, 2));
  return manifest.length;
}

await mkdir(rawDir, { recursive: true });
await mkdir(renderedDir, { recursive: true });

const rootResponse = await fetch(`${sourceBase.replace(/\/$/, '')}/wp-json`);
if (!rootResponse.ok) {
  throw new Error(`Failed to fetch root WP API: ${rootResponse.status} ${rootResponse.statusText}`);
}

const root = await rootResponse.json();
await writeFile(path.join(rawDir, 'site.json'), JSON.stringify(root, null, 2));

const manifest = {
  source_url: sourceBase,
  generated_at: new Date().toISOString(),
  endpoints: {},
  media_downloaded: false,
  media_download_count: 0,
};

for (const [name, endpoint] of endpointDefinitions) {
  const result = await getAll(endpoint);
  manifest.endpoints[name] = {
    count: result.rows.length,
    reported_total: result.total,
    total_pages: result.totalPages,
  };
  await writeFile(path.join(rawDir, `${name}.json`), JSON.stringify(result.rows, null, 2));

  if (name === 'pages' || name === 'posts') {
    await writeRenderedHtml(name, result.rows);
  }
}

const mediaRows = JSON.parse(await readFile(path.join(rawDir, 'media.json'), 'utf8'));
if (downloadMedia) {
  manifest.media_downloaded = true;
  manifest.media_download_count = await downloadMediaFiles(mediaRows);
}

await writeFile(path.join(outDir, 'manifest.json'), JSON.stringify(manifest, null, 2));

console.log(`Fetched current site content into ${path.relative(rootDir, outDir)}`);
console.log(JSON.stringify(manifest, null, 2));
