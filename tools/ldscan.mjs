// Node >=18, ESM. Scans theme tree, extracts @ld-tags/@ld-id/@ld-deps.
// Produces build/ld-manifest.json with file metadata.

import { promises as fs } from 'node:fs';
import { createHash } from 'node:crypto';
import path from 'node:path';

const ROOT = path.resolve(process.cwd());
const THEME_DIR = ROOT;
const OUT = path.join(THEME_DIR, 'build/ld-manifest.json');

const INCLUDE_EXT = new Set(['.php','.scss','.css','.js','.json','.md']);
const IGNORE_DIRS = new Set(['.git','node_modules','build','.vite']);

const TAG_RE  = /@ld-tags:\s*([^\n*]+)/i;
const ID_RE   = /@ld-id:\s*([^\n*]+)/i;
const DEPS_RE = /@ld-deps:\s*([^\n*]+)/i;

// cheap heuristics to guess deps
const GUESSERS = [
  { re:/\bimport\s+[^'"]*['"]([^'"]+)['"]/g, kind:'import' },
  { re:/@use\s+['"]([^'"]+)['"]/g, kind:'sass-use' },
  { re:/get_template_part\(\s*['"]([^'"]+)['"]/g, kind:'wp-tpl' }
];

async function* walk(dir) {
  const entries = await fs.readdir(dir, { withFileTypes:true });
  for (const e of entries) {
    if (IGNORE_DIRS.has(e.name)) continue;
    const full = path.join(dir, e.name);
    if (e.isDirectory()) yield* walk(full);
    else yield full;
  }
}

async function sha1(file) {
  const buf = await fs.readFile(file);
  const h = createHash('sha1'); h.update(buf); return h.digest('hex').slice(0,12);
}

function rel(p){ return path.relative(THEME_DIR, p).replaceAll('\\','/'); }

function parseMeta(src) {
  const m = {};
  const tag = src.match(TAG_RE);
  if (tag) m.tags = tag[1].split(',').map(s=>s.trim()).filter(Boolean);
  const id = src.match(ID_RE);
  if (id) m.id = id[1].trim();
  const deps = src.match(DEPS_RE);
  if (deps) m.deps = deps[1].split(',').map(s=>s.trim()).filter(Boolean);

  // guess deps
  const g = new Set(m.deps || []);
  for (const guess of GUESSERS) {
    let m2;
    const re = new RegExp(guess.re);
    while ((m2 = re.exec(src))) {
      g.add(m2[1]);
    }
  }
  if (g.size) m.deps = [...g];
  return m;
}

async function main() {
  const items = [];
  for await (const full of walk(THEME_DIR)) {
    const ext = path.extname(full).toLowerCase();
    if (!INCLUDE_EXT.has(ext)) continue;
    const relative = rel(full);
    const stat = await fs.stat(full);
    const src = await fs.readFile(full, 'utf8').catch(()=> '');
    const meta = parseMeta(src);
    const hash = await sha1(full);
    items.push({
      file: relative,
      size: stat.size,
      hash,
      tags: meta.tags || ['untagged'],
      id: meta.id || null,
      deps: meta.deps || []
    });
  }
  const index = {
    theme: path.basename(THEME_DIR),
    generatedAt: new Date().toISOString(),
    items,
  };
  await fs.mkdir(path.dirname(OUT), { recursive:true });
  await fs.writeFile(OUT, JSON.stringify(index, null, 2), 'utf8');
  console.log(`ldscan → ${rel(OUT)} (${items.length} files)`);
}

main().catch(e=>{ console.error(e); process.exit(1); });