// assets/admin/icon-preview.js
(function () {
  const use = (id) =>
    id ? `<svg aria-hidden="true"><use href="#${id}"></use></svg>` : "";

  function enhanceSelect2(sel) {
    if (!sel) return;
    // ACF Select2 v4
    const $ = window.jQuery;
    if (!$.fn || !$.fn.select2) return; // на случай кэш/рассинхрон

    const $el = $(sel);
    const templateResult = (data) => {
      // у ACF data.id === option.value (наши ids: glyph-*/brand-*)
      if (!data.id) return data.text;
      return `<span class="ld-icon-opt">${use(data.id)}${data.text || ""}</span>`;
    };
    const templateSelection = (data) => {
      if (!data.id) return data.text || "";
      return `<span class="ld-icon-sel">${use(data.id)}${data.text || ""}</span>`;
    };

    if ($el.data("select2")) $el.select2("destroy");
    $el.select2({
      width: "100%",
      templateResult,
      templateSelection,
      escapeMarkup: (m) => m,
    });
  }

  function wireSourceRadios(scope) {
    const root = scope || document;
    const wrapTheme = root.querySelector('[data-ld="icon-theme-wrap"]');
    const wrapMedia = root.querySelector('[data-ld="icon-media-wrap"]');
    const radios = root.querySelectorAll(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
    );
    if (!radios.length) return;

    const apply = () => {
      const val = Array.from(radios).find((r) => r.checked)?.value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");
    };
    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  function enhanceMediaSvgPreview(scope) {
    const root = scope || document;
    const uploader = root.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!uploader) return;

    const imgWrap = uploader.querySelector(".image-wrap");

    const ensureHolder = () => {
      let holder = uploader.querySelector(".ld-media-inline");
      if (!holder) {
        holder = document.createElement("div");
        holder.className = "ld-media-inline";
        uploader.appendChild(holder);
      }
      return holder;
    };

    const renderInline = (url) => {
      fetch(url)
        .then((r) => r.text())
        .then((svg) => {
          svg = svg
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');
          const holder = ensureHolder();
          holder.innerHTML = svg;
          if (imgWrap) imgWrap.classList.add("ld-hide-img"); // << прячем IMG
        })
        .catch(() => {});
    };

    const clearInline = () => {
      const holder = uploader.querySelector(".ld-media-inline");
      if (holder) holder.innerHTML = "";
      if (imgWrap) imgWrap.classList.remove("ld-hide-img");
    };

    const sync = () => {
      const img = uploader.querySelector(".image-wrap img");
      if (img && /\.svg(\?|#|$)/i.test(img.src)) renderInline(img.src);
      else clearInline();
    };

    // начальная синхронизация
    sync();

    // наблюдаем любые изменения в аплоадере (после выбора/remove)
    new MutationObserver(sync).observe(uploader, {
      subtree: true,
      childList: true,
      attributes: true,
    });
  }

  function init(scope) {
    const root = scope || document;

    // простые селекты (без попытки «засунуть» иконку внутрь самого контрола)
    root
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select,' +
          '.acf-field[data-name="post_icon_name"] select,' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(enhanceSelect2);

    wireSourceRadios(root);
    enhanceMediaSvgPreview(root);
  }

  // Гарантированно после ACF:
  if (window.acf && window.acf.add_action) {
    window.acf.add_action("ready", ($el) =>
      init($el && $el[0] ? $el[0] : document),
    );
    window.acf.add_action("append", ($el) =>
      init($el && $el[0] ? $el[0] : document),
    );
    // На всякий — отдельный хук инициализации селекта от ACF:
    window.acf.add_action("select2_init", function ($select) {
      enhanceSelect2($select);
    });
  } else {
    // fallback
    if (document.readyState !== "loading") init();
    else document.addEventListener("DOMContentLoaded", init);
  }
})();
