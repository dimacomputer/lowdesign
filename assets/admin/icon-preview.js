// assets/admin/icon-preview.js
(function () {
  // ---- Select2 с иконками --------------------------------------------------
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

    // (пере)инициализация
    if ($el.data("select2")) $el.select2("destroy");
    $el.select2({
      width: "100%",
      templateResult,
      templateSelection,
      escapeMarkup: (m) => m,
    });
  }

  function initSelects(root) {
    const scope = root || document;
    scope
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select, ' +
          '.acf-field[data-name="post_icon_name"] select, ' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(ensureSelect2WithIcons);
  }

  // ---- Переключение источника (radio) --------------------------------------
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
      if (val !== "media" && wrapMedia) {
        // очистка инлайна при уходе с media
        const iw = wrapMedia.querySelector(".acf-image-uploader .image-wrap");
        iw?.querySelector(".ld-media-inline")?.remove();
        iw?.querySelector("img")?.classList.remove("ld-hide-img");
      }
    };

    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  // ---- Инлайн превью для загруженного SVG ----------------------------------
  function enhanceMediaSvgPreview(root) {
    const scope = root || document;
    const mediaWrap = scope.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!mediaWrap) return;

    const imageWrap = mediaWrap.querySelector(".image-wrap");
    if (!imageWrap) return;

    const innerActions =
      imageWrap.querySelector(".acf-actions") || imageWrap.lastElementChild;

    const holder =
      imageWrap.querySelector(".ld-media-inline") ||
      document.createElement("div");
    holder.className = "ld-media-inline";

    const renderInlineFromUrl = (url) => {
      if (!/\.svg(\?|#|$)/i.test(url)) {
        // не SVG — убираем инлайн, показываем стандартную картинку
        holder.remove();
        imageWrap.querySelector("img")?.classList.remove("ld-hide-img");
        return;
      }
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // убрать <script>, любые fill и проставить currentColor
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');
          holder.innerHTML = svg;

          if (!holder.parentNode) {
            // вставляем ПЕРЕД actions, чтобы был один аккуратный превью
            if (innerActions) imageWrap.insertBefore(holder, innerActions);
            else imageWrap.appendChild(holder);
          }
          const img = imageWrap.querySelector("img");
          if (img) img.classList.add("ld-hide-img"); // прячем только <img>, не кнопки
        })
        .catch(() => {});
    };

    const sync = () => {
      const img = imageWrap.querySelector("img");
      if (img && img.src) renderInlineFromUrl(img.src);
      else {
        holder.remove();
        imageWrap.querySelector("img")?.classList.remove("ld-hide-img");
      }
    };

    // первичный запуск
    sync();

    // реакция на клики в аплоадере (Add/Replace/Remove)
    mediaWrap.addEventListener("click", () => setTimeout(sync, 200));
  }

  // ---- boot ---------------------------------------------------------------
  function boot(root) {
    initSelects(root);
    wireSourceRadios(root);
    enhanceMediaSvgPreview(root);
  }

  if (document.readyState !== "loading") boot();
  else document.addEventListener("DOMContentLoaded", boot);

  // ACF перерисовывает поля динамически — цепляемся к его событиям
  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", function ($el) {
      boot($el ? $el[0] : document);
    });
    window.acf.addAction("append", function ($el) {
      boot($el ? $el[0] : document);
    });
  }
})();
