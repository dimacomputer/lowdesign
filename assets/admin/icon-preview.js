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
      holder.className = "ld-icon-preview ld-ml-1"; // небольшой spacer
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

  // инлайн превью для SVG из Медиа (внутри поля image), не ломая ACF-кнопки
  function enhanceMediaSvgPreview() {
    const wrap = document.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!wrap) return;

    const getImg = () => wrap.querySelector(".image-wrap img");

    const renderInline = (url) => {
      if (!url) return clearInline();
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // sanitize + currentColor
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');

          // прячем ТОЛЬКО <img>, оставляя .image-wrap и acf-actions видимыми
          const img = getImg();
          if (img) img.classList.add("ld-hide-img");

          let holder = wrap.querySelector(".image-wrap .ld-media-inline");
          if (!holder) {
            const imageWrap = wrap.querySelector(".image-wrap") || wrap;
            holder = document.createElement("div");
            holder.className = "ld-media-inline";
            imageWrap.appendChild(holder);
          }
          holder.innerHTML = svg;
        })
        .catch(() => {
          /* noop */
        });
    };

    const clearInline = () => {
      wrap.querySelector(".image-wrap .ld-media-inline")?.remove();
      const img = getImg();
      if (img) img.classList.remove("ld-hide-img");
    };

    // первичная инициализация
    const currentImg = getImg();
    if (currentImg && /\.svg(\?|#|$)/i.test(currentImg.src)) {
      renderInline(currentImg.src);
    }

    // ловим изменения выбора/удаления (после кликов по Edit/Remove/Add Image)
    document.addEventListener("click", (e) => {
      if (!wrap.contains(e.target)) return;
      setTimeout(() => {
        const img = getImg();
        if (img && /\.svg(\?|#|$)/i.test(img.src)) {
          renderInline(img.src);
        } else {
          clearInline();
        }
      }, 80);
    });

    // если ACF динамически перерисует DOM
    const mo = new MutationObserver(() => {
      const img = getImg();
      if (img && /\.svg(\?|#|$)/i.test(img.src)) {
        renderInline(img.src);
      } else {
        clearInline();
      }
    });
    mo.observe(wrap, { subtree: true, childList: true, attributes: false });
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

    // превью SVG из медиа — без скрытия ACF-кнопок
    enhanceMediaSvgPreview();
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
