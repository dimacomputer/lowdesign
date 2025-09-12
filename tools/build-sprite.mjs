// tools/build-sprite.mjs
import fg from 'fast-glob';
import fs from 'fs-extra';
import { optimize } from 'svgo';
import svgstore from 'svgstore';
import path from 'path';

const SRC = 'assets/icons/src';
const OUT = 'assets/icons/sprite.svg';

const files = await fg('**/*.svg', { cwd: SRC, dot: false });
if (!files.length) {
  await fs.outputFile(OUT, '<svg xmlns="http://www.w3.org/2000/svg"></svg>\n');
  console.log('No icons found, wrote empty sprite.');
  process.exit(0);
}

const store = svgstore({});

for (const rel of files) {
  const abs = path.join(SRC, rel);
  const raw = await fs.readFile(abs, 'utf8');

  // оптимизируем ещё раз на всякий случай (svgo уже в пайплайне)
  const { data } = optimize(raw, {
    path: abs,
    multipass: true,
  });

  // id = имя файла без .svg
  const id = path.basename(rel, '.svg'); // ожидаем 'icon-<area>-<name>'
  store.add(id, data, { copyAttrs: false });
}

const sprite =
  '<!-- Generated from assets/icons/src by tools/build-sprite.mjs -->\n' +
  store.toString({ inline: true }) + '\n';

await fs.outputFile(OUT, sprite);
console.log(`Sprite written: ${OUT} (${files.length} symbols)`);
