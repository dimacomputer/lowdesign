(function () {
  // ---------------- helpers ----------------
  function ensureSelect2WithIcons(sel) {
    if (!sel) return;
    const $ = window.jQuery;
    const $el = $(sel);
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

  function initSelects(scope) {
    const root = scope || document;
    root
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select,' +
          '.acf-field[data-name="post_icon_name"] select,' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(ensureSelect2WithIcons);
  }

  // radio → показать/скрыть блоки выбора источника
  function wireSourceRadios(scope) {
    const root = scope || document;
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

  // инлайн превью SVG для поля upload (ACF image)
  function enhanceMediaSvgPreview(scope) {
    const root = scope || document;
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

          const holder =
            mediaWrap.querySelector(".ld-media-inline") ||
            document.createElement("div");
          holder.className = "ld-media-inline";
          holder.innerHTML = svg;

          mediaWrap.querySelector(".image-wrap")?.classList.add("ld-hidden");
          mediaWrap.appendChild(holder);
        })
        .catch(() => {});
    };

    const refresh = () => {
      const img = mediaWrap.querySelector(".image-wrap img");
      const holder = mediaWrap.querySelector(".ld-media-inline");
      if (img && /\.svg(\?|#|$)/i.test(img.src)) {
        renderInline(img.src);
      } else {
        mediaWrap.querySelector(".image-wrap")?.classList.remove("ld-hidden");
        holder?.remove();
      }
    };

    // первичная попытка
    refresh();

    // реакции на выбор/удаление
    root.addEventListener("click", (e) => {
      if (!mediaWrap.contains(e.target)) return;
      setTimeout(refresh, 60);
    });
  }

  // ---------------- bootstrap ----------------
  function init(scope) {
    initSelects(scope);
    wireSourceRadios(scope);
    enhanceMediaSvgPreview(scope);
  }

  // 1) первый прогон
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", () => init());

  // 2) ACF (Гутенберг): поля появляются динамически
  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", (ctx) =>
      init(ctx && ctx[0] ? ctx[0] : document),
    );
    window.acf.addAction("append", (ctx) =>
      init(ctx && ctx[0] ? ctx[0] : document),
    );
  }
})();
