(function () {
  // ---- helpers -------------------------------------------------------------
  function ensureSelect2WithIcons(sel) {
    if (!sel) return;
    // templates: icon left in dropdown & in selection
    const renderIcon = (id) =>
      id ? `<svg aria-hidden="true"><use href="#${id}"></use></svg>` : "";
    const templateResult = (data) => {
      if (!data.id) return data.text;
      return `<span class="ld-icon-opt">${renderIcon(data.id)}${data.text}</span>`;
    };
    const templateSelection = (data) => {
      if (!data.id) return data.text || "";
      return `<span class="ld-icon-sel">${renderIcon(data.id)}${data.text}</span>`;
    };

    // init (if not already)
    const $ = window.jQuery;
    const $el = $(sel);
    if (!$el.data("select2")) {
      $el.select2({
        width: "100%",
        templateResult,
        templateSelection,
        escapeMarkup: (m) => m,
      });
    } else {
      // force rerender on existing
      $el.select2("destroy");
      $el.select2({
        width: "100%",
        templateResult,
        templateSelection,
        escapeMarkup: (m) => m,
      });
    }
  }

  // toggle blocks by radio
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

  // inline SVG preview for ACF image field when SVG is selected
  function enhanceMediaSvgPreview() {
    // ACF делает превью в .acf-image-uploader .image-wrap > img
    const mediaWrap = document.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!mediaWrap) return;

    const renderInline = (url) => {
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // удалить потенциальные fill, проставить currentColor, задать класс
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');
          const holder =
            mediaWrap.querySelector(".ld-media-inline") ||
            document.createElement("div");
          holder.className = "ld-media-inline";
          holder.innerHTML = svg;
          mediaWrap.querySelector(".image-wrap")?.classList.add("ld-hidden"); // скрыть стандартный превью IMG
          mediaWrap.appendChild(holder);
        })
        .catch(() => {
          /* no-op */
        });
    };

    // первичная попытка — если уже есть выбранная картинка
    const currentImg = mediaWrap.querySelector(".image-wrap img");
    if (currentImg && /\.svg(\?|#|$)/i.test(currentImg.src)) {
      renderInline(currentImg.src);
    }

    // слушаем изменения поля (ACF бросает события)
    document.addEventListener("click", (e) => {
      // ловим клик "Select Image"/"Remove"
      if (!mediaWrap.contains(e.target)) return;
      setTimeout(() => {
        const img = mediaWrap.querySelector(".image-wrap img");
        const holder = mediaWrap.querySelector(".ld-media-inline");
        if (img && /\.svg(\?|#|$)/i.test(img.src)) {
          renderInline(img.src);
        } else {
          // не SVG — показать обычный img, убрать наш inline
          mediaWrap.querySelector(".image-wrap")?.classList.remove("ld-hidden");
          holder?.remove();
        }
      }, 50);
    });
  }

  function init() {
    // 1) только select2-иконки, БЕЗ отдельного .icon-preview
    const selects = document.querySelectorAll(
      '.acf-field[data-name="menu_icon"] select, ' +
        '.acf-field[data-name="post_icon_name"] select, ' +
        '.acf-field[data-name="term_icon_name"] select',
    );
    selects.forEach(ensureSelect2WithIcons);

    // 2) переключалки источника
    wireSourceRadios();

    // 3) инлайн превью SVG из медиа, если выбран svg
    enhanceMediaSvgPreview();
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
