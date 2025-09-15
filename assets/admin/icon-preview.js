// assets/admin/icon-preview.js
(function () {
  // === настройки ============================================================
  // false = показываем стандартный превью ACF (img), инлайн SVG отключён
  // true  = инлайн SVG (recolor currentColor), стандартный img скрыт
  const USE_INLINE_MEDIA_PREVIEW = false;

  // === select2 с иконками (спрайт) ==========================================
  function ensureSelect2WithIcons(sel) {
    if (!sel) return;
    const $ = window.jQuery;
    const $el = $(sel);

    const renderIcon = (id) =>
      id ? `<svg aria-hidden="true"><use href="#${id}"></use></svg>` : "";

    const templateResult = (d) =>
      d.id
        ? `<span class="ld-icon-opt">${renderIcon(d.id)}${d.text}</span>`
        : d.text;

    const templateSelection = (d) =>
      d.id
        ? `<span class="ld-icon-sel">${renderIcon(d.id)}${d.text}</span>`
        : d.text || "";

    if ($el.data("select2")) $el.select2("destroy");
    $el.select2({
      width: "100%",
      templateResult,
      templateSelection,
      escapeMarkup: (m) => m,
    });
  }

  function initSelects(root) {
    (root || document)
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select, ' +
          '.acf-field[data-name="post_icon_name"] select, ' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(ensureSelect2WithIcons);
  }

  // === radio переключатель источника =======================================
  function wireSourceRadios(root) {
    const scope = root || document;
    const wrapTheme = scope.querySelector('[data-ld="icon-theme-wrap"]');
    const wrapMedia = scope.querySelector('[data-ld="icon-media-wrap"]');
    const radios = scope.querySelectorAll(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
    );
    if (!radios.length) return;

    const apply = () => {
      const val = [...radios].find((r) => r.checked)?.value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");

      // при уходе с media гарантируем, что виден нативный img и нет нашего инлайна
      if (val !== "media" && wrapMedia) {
        const iw = wrapMedia.querySelector(".acf-image-uploader .image-wrap");
        iw?.querySelector(".ld-media-inline")?.remove();
        iw?.querySelector("img")?.classList.remove("ld-hide-img");
      }
    };

    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  // === превью Media Library =================================================
  function enhanceMediaSvgPreview(root) {
    const scope = root || document;
    const uploader = scope.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!uploader) return;

    const imageWrap = uploader.querySelector(".image-wrap");
    if (!imageWrap) return;

    const actions =
      imageWrap.querySelector(".acf-actions") || imageWrap.lastElementChild;

    const holder =
      imageWrap.querySelector(".ld-media-inline") ||
      document.createElement("div");
    holder.className = "ld-media-inline";

    const renderInlineFromUrl = (url) => {
      if (!/\.svg(\?|#|$)/i.test(url)) {
        holder.remove();
        imageWrap.querySelector("img")?.classList.remove("ld-hide-img");
        return;
      }
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');
          holder.innerHTML = svg;
          if (!holder.parentNode) {
            if (actions) imageWrap.insertBefore(holder, actions);
            else imageWrap.appendChild(holder);
          }
          const img = imageWrap.querySelector("img");
          if (img) img.classList.add("ld-hide-img");
        })
        .catch(() => {});
    };

    const showNativeOnly = () => {
      holder.remove();
      const img = imageWrap.querySelector("img");
      if (img) img.classList.remove("ld-hide-img");
    };

    const sync = () => {
      const img = imageWrap.querySelector("img");
      if (!USE_INLINE_MEDIA_PREVIEW) {
        showNativeOnly();
        return;
      }
      if (img && img.src) renderInlineFromUrl(img.src);
      else showNativeOnly();
    };

    // первичная отрисовка + реакция на клики Add/Replace/Remove
    sync();
    uploader.addEventListener("click", () => setTimeout(sync, 200));
  }

  // === boot ================================================================
  function boot(root) {
    initSelects(root);
    wireSourceRadios(root);
    enhanceMediaSvgPreview(root);
  }

  if (document.readyState !== "loading") boot();
  else document.addEventListener("DOMContentLoaded", boot);

  // пересоздание при динамических рендерах ACF
  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", ($el) => boot($el ? $el[0] : document));
    window.acf.addAction("append", ($el) => boot($el ? $el[0] : document));
  }
})();
