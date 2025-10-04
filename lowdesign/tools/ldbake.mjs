// Creates a slim project theme from lowdesign using tags/globs.
// Usage: node tools/ldbake.mjs project.margo.json

import { promises as fs } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = process.cwd();
const MANIFEST = path.join(ROOT, 'build/ld-manifest.json');

function rel(p){ return path.relative(ROOT, p).replaceAll('\\','/'); }

async function readJSON(p){ return JSON.parse(await fs.readFile(p,'utf8')); }

function matchAny(str, globs=[]) {
  // very small glob (*, **, suffix) for our purposes
  return globs.some(g => {
    if (g === '**') return true;
    if (g.endsWith('/**')) return str.startsWith(g.slice(0,-3));
    if (g.startsWith('**/')) return str.endsWith(g.slice(3));
    if (g.includes('*')) {
      const re = new RegExp('^' + g.replace(/\./g,'\\.').replace(/\*\*/g,'.*').replace(/\*/g,'[^/]*') + '$');
      return re.test(str);
    }
    return str === g;
  });
}

async function copyFile(src, dst) {
  await fs.mkdir(path.dirname(dst), { recursive:true });
  await fs.copyFile(src, dst);
}

async function main() {
  const configPath = process.argv[2] || 'project.json';
  const cfg = await readJSON(configPath);

  const {
    projectSlug,                     // e.g. "lowdesign-margo"
    outDir,                          // e.g. "../lowdesign-margo"
    includeTags = [],                // e.g. ["core","header","footer","search","acf"]
    includeGlobs = [],               // e.g. ["functions.php","header.php","footer.php","assets/src/js/**", ...]
    always = ["style.css","index.php","functions.php","screenshot.jpg"],
    renameTheme = { styleName: null, textDomain: null },
    stripUntagged = true
  } = cfg;

  const manifest = await readJSON(MANIFEST);
  const all = manifest.items;

  // Decide which files to include
  const picked = [];
  for (const it of all) {
    const taggedOk = includeTags.length ? it.tags.some(t => includeTags.includes(t)) : !stripUntagged || it.tags.includes('untagged');
    const globOk = includeGlobs.length ? matchAny(it.file, includeGlobs) : false;
    const must = always.includes(it.file);
    if (taggedOk || globOk || must) picked.push(it.file);
  }

  // Also include dependencies of picked (best effort)
  const deps = new Set();
  for (const it of all) {
    if (!picked.includes(it.file)) continue;
    for (const d of (it.deps || [])) {
      // normalize relative imports from SCSS/JS like './site' → try to resolve quickly
      if (d.startsWith('./') || d.startsWith('../')) {
        const base = path.dirname(it.file);
        let guess = path.join(base, d);
        const tryExt = ['','.php','.scss','.css','.js'];
        for (const ex of tryExt) {
          const candidate = rel(path.join(ROOT, guess + ex));
          if (all.find(x => x.file === candidate)) { deps.add(candidate); break; }
        }
      }
    }
  }
  const finalSet = new Set([...picked, ...deps, ...always]);

  // Copy
  const OUT = path.resolve(outDir);
  await fs.rm(OUT, { recursive:true, force:true });
  await fs.mkdir(OUT, { recursive:true });

  for (const f of finalSet) {
    const src = path.join(ROOT, f);
    const dst = path.join(OUT, f);
    await copyFile(src, dst);
  }

  // Minimal style.css patch (name/text-domain)
  if (renameTheme.styleName || renameTheme.textDomain) {
    const p = path.join(OUT, 'style.css');
    try {
      let css = await fs.readFile(p,'utf8');
      if (renameTheme.styleName) css = css.replace(/Theme Name:[^\n]*/i, `Theme Name: ${renameTheme.styleName}`);
      if (renameTheme.textDomain) css = css.replace(/Text Domain:[^\n]*/i, `Text Domain: ${renameTheme.textDomain}`);
      await fs.writeFile(p, css, 'utf8');
    } catch {}
  }

  console.log(`ldbake → ${outDir}\nFiles: ${finalSet.size}`);
}

main().catch(e=>{ console.error(e); process.exit(1); });