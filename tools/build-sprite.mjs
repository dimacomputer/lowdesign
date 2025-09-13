import fg from "fast-glob";
import fs from "fs-extra";
import svgstore from "svgstore";

const SRC = "assets/icons/src";
const OUT = "assets/icons/sprite.svg";

(async () => {
  const files = await fg(["**/*.svg"], { cwd: SRC, absolute: true });
  if (!files.length) {
    console.error("No SVGs found in", SRC);
    process.exit(1);
  }

  const sprites = svgstore({ cleanSymbols: true });

  for (const file of files) {
    // id = относительный путь без .svg, например ui/icon-ui-menu
    const id = file
      .replace(/^.*\/assets\/icons\/src\//, "")
      .replace(/\.svg$/, "")
      .replace(/[^\w\-:.]/g, "-");

    const svg = await fs.readFile(file, "utf8");
    sprites.add(id, svg);
  }

  await fs.outputFile(OUT, sprites.toString({ pretty: true }));
  console.log(`Sprite written: ${OUT} (${files.length} symbols)`);
})();
