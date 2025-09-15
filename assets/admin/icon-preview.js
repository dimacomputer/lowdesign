(function () {
  // helpers ---------------------------------------------------------------
  function createSvgUse(id) {
    return id ? `<svg aria-hidden="true"><use href="#${id}"></use></svg>` : "";
  }

  function ensureSpritePreview(select) {
    if (!select) return;
    const field = select.closest(".acf-field");
    if (!field) return;

    // создаём/находим место под превью справа от селекта
    let holder = field.querySelector(".ld-icon-preview");
    if (!holder) {
      holder = document.createElement("span");
      holder.className = "ld-icon-preview";
      select.after(holder);
    }

    const apply = () => {
      const id = select.value || "";
      holder.innerHTML = id ? createSvgUse(id) : "";
      holder.classList.toggle("ld-hidden", !id);
    };

    select.addEventListener("change", apply);
    apply(); // init
  }

  // показать/скрыть блоки по радио
  function wireSourceRadios() {
    const wrapTheme = document.querySelector('[data-ld="icon-theme-wrap"]');
    const wrapMedia = document.querySelector('[data-ld="icon-media-wrap"]');
    const radios = document.querySelectorAll(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
    );
    const apply = () => {
      const val = [...radios].find((r) => r.checked)?.value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");
    };
    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  // инлайн превью для SVG из Медиа (внутри поля image)
  function enhanceMediaSvgPreview() {
    const wrap = document.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!wrap) return;

    const renderInline = (url) => {
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // вычищаем, ставим currentColor
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');

          // прячем дефолтный IMG
          const imgWrap = wrap.querySelector(".image-wrap");
          if (imgWrap) imgWrap.classList.add("ld-hide");

          let holder = wrap.querySelector(".ld-media-inline");
          if (!holder) {
            holder = document.createElement("div");
            holder.className = "ld-media-inline";
            wrap.appendChild(holder);
          }
          holder.innerHTML = svg;
        })
        .catch(() => {
          /* noop */
        });
    };

    const clearInline = () => {
      wrap.querySelector(".ld-media-inline")?.remove();
      wrap.querySelector(".image-wrap")?.classList.remove("ld-hide");
    };

    // первичная инициализация
    const currentImg = wrap.querySelector(".image-wrap img");
    if (currentImg && /\.svg(\?|#|$)/i.test(currentImg.src)) {
      renderInline(currentImg.src);
    }

    // ловим изменения выбора/удаления
    document.addEventListener("click", (e) => {
      if (!wrap.contains(e.target)) return;
      setTimeout(() => {
        const img = wrap.querySelector(".image-wrap img");
        if (img && /\.svg(\?|#|$)/i.test(img.src)) {
          renderInline(img.src);
        } else {
          clearInline();
        }
      }, 50);
    });
  }

  function init() {
    // превью для sprite-селектов (без select2)
    document
      .querySelectorAll(
        '.acf-field[data-name="post_icon_name"] select,' +
          '.acf-field[data-name="term_icon_name"] select,' +
          '.acf-field[data-name="menu_icon"] select',
      )
      .forEach(ensureSpritePreview);

    // радио-переключалки
    wireSourceRadios();

    // превью SVG из медиа
    enhanceMediaSvgPreview();
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
