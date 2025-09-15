(function () {
  // ---- helpers -------------------------------------------------------------
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
      $el.select2("destroy").select2({
        width: "100%",
        templateResult,
        templateSelection,
        escapeMarkup: (m) => m,
      });
    }
  }

  // Показ/скрытие блоков по радио
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

  // Inline SVG превью для выбранного SVG; иначе — показываем обычный IMG (оба 24px)
  function enhanceMediaSvgPreview() {
    const uploader = document.querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!uploader) return;

    const imageWrap = uploader.querySelector(".image-wrap");
    if (!imageWrap) return;

    const getImg = () => imageWrap.querySelector("img");
    const getHolder = () => uploader.querySelector(".ld-media-inline");

    const isSvgUrl = (url) => /\.svg(\?|#|$)/i.test(url || "");

    const removeInline = () => {
      const holder = getHolder();
      if (holder) holder.remove();
      imageWrap.classList.remove("ld-hidden");
    };

    const renderInlineSvg = (url) => {
      // Удаляем старую inline-вставку, чтобы не было дублей
      const prev = getHolder();
      if (prev) prev.remove();

      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // чистим и обеспечиваем currentColor
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/^<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">');

          const holder = document.createElement("div");
          holder.className = "ld-media-inline";
          holder.innerHTML = svg;

          // скрываем IMG и показываем только inline
          imageWrap.classList.add("ld-hidden");
          uploader.appendChild(holder);
        })
        .catch(() => {
          // если не удалось подтянуть svg — возвращаемось к обычному IMG
          removeInline();
        });
    };

    const applyState = () => {
      const img = getImg();
      const src = img?.src || "";
      if (src && isSvgUrl(src)) {
        renderInlineSvg(src);
      } else {
        removeInline();
      }
    };

    // Первичный прогон
    applyState();

    // Слежение за изменениями превью через MutationObserver (надёжно для ACF)
    const mo = new MutationObserver(() => applyState());
    mo.observe(imageWrap, { childList: true, subtree: true, attributes: true });

    // На всякий случай ловим клики внутри аплоадера (Select/Remove) и применяем спустя тик
    uploader.addEventListener("click", () => setTimeout(applyState, 50));
  }

  function init() {
    // select2 с иконками
    document
      .querySelectorAll(
        '.acf-field[data-name="menu_icon"] select, ' +
          '.acf-field[data-name="post_icon_name"] select, ' +
          '.acf-field[data-name="term_icon_name"] select',
      )
      .forEach(ensureSelect2WithIcons);

    // переключалки источника
    wireSourceRadios();

    // единая превьюшка для медиа SVG/IMG
    enhanceMediaSvgPreview();
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
