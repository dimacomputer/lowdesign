(function () {
  // ---------------- helpers ----------------
  function ensureSelect2WithIcons(sel) {
    if (!sel) return;
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

    const $ = window.jQuery,
      $el = $(sel);
    const opts = {
      width: "100%",
      templateResult,
      templateSelection,
      escapeMarkup: (m) => m,
    };
    if ($el.data("select2")) {
      $el.select2("destroy");
    }
    $el.select2(opts);
  }

  // переключаем блоки по радиокнопкам
  function wireSourceRadios(ctx) {
    const root = ctx || document;
    const wrapTheme = root.querySelector('[data-ld="icon-theme-wrap"]');
    const wrapMedia = root.querySelector('[data-ld="icon-media-wrap"]');
    const radios = root.querySelectorAll(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
    );
    if (!radios.length) return;
    const apply = () => {
      const val = [...radios].find((r) => r.checked)?.value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");
    };
    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  // инлайн превью SVG из медиа, чтобы красилось currentColor
  function enhanceMediaSvgPreview(ctx) {
    const root = ctx || document;
    const mediaWrap = root.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!mediaWrap) return;

    const renderInline = (url) => {
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');
          let holder = mediaWrap.querySelector(".ld-media-inline");
          if (!holder) {
            holder = document.createElement("div");
            holder.className = "ld-media-inline";
            mediaWrap.appendChild(holder);
          }
          holder.innerHTML = svg;
          mediaWrap.querySelector(".image-wrap")?.classList.add("ld-hidden");
        })
        .catch(() => {});
    };

    const sync = () => {
      const img = mediaWrap.querySelector(".image-wrap img");
      const holder = mediaWrap.querySelector(".ld-media-inline");
      if (img && /\.svg(\?|#|$)/i.test(img.src)) {
        renderInline(img.src);
      } else {
        mediaWrap.querySelector(".image-wrap")?.classList.remove("ld-hidden");
        holder?.remove();
      }
    };

    // первичная
    sync();

    // клики по “Select/Remove”
    mediaWrap.addEventListener("click", () => setTimeout(sync, 50));
  }

  function init(ctx) {
    const root = ctx || document;

    // 1) Select2-иконки (без отдельного превью-элемента)
    root
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select, ' +
          '.acf-field[data-name="post_icon_name"] select, ' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(ensureSelect2WithIcons);

    // 2) Радио-переключатели источника
    wireSourceRadios(root);

    // 3) Инлайн превью из медиа (SVG)
    enhanceMediaSvgPreview(root);
  }

  // инициализация
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", () => init());

  // ACF события (страницы терминов/редактор, append полей и т.п.)
  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", init);
    window.acf.addAction("append", init);
  }

  // Переподключать после AJAX (термины, быстрая правка и т.п.)
  if (window.jQuery) {
    jQuery(document).ajaxComplete(() => init());
  }
})();
