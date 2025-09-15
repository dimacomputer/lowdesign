(function () {
  // --- utils ---------------------------------------------------------------
  function $$(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }
  function on(el, ev, cb) {
    el && el.addEventListener(ev, cb, { passive: true });
  }

  // select2: простой дропдаун с поиском, БЕЗ картинок
  function initPlainSelect2(select) {
    if (!select || !window.jQuery || !jQuery.fn.select2) return;
    const $el = jQuery(select);
    if ($el.data("select2")) {
      $el.select2("destroy");
    }
    $el.select2({ width: "100%" });
  }

  // переключатель источника
  function wireSourceRadios(scope) {
    const wrapTheme = (scope || document).querySelector(
      '[data-ld="icon-theme-wrap"]',
    );
    const wrapMedia = (scope || document).querySelector(
      '[data-ld="icon-media-wrap"]',
    );
    const radios = $$(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
      scope,
    );

    const apply = () => {
      const val = radios.find((r) => r.checked)?.value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");
    };

    radios.forEach((r) => on(r, "change", apply));
    apply();
  }

  // инлайн превью SVG для поля аплоада
  function enhanceMediaSvgPreview(scope) {
    const mediaWrap = (scope || document).querySelector(
      '[data-ld="icon-media-wrap"] .acf-image-uploader',
    );
    if (!mediaWrap) return;

    function renderInline(url) {
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // прибираемся: выкинуть скрипты, убрать fill, принудить currentColor
          let svg = txt
            .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
            .replace(/\sfill="[^"]*"/gi, "")
            .replace(/<svg\b/i, '<svg fill="currentColor"');

          // контейнер для инлайн-превью
          let holder = mediaWrap.querySelector(".ld-media-inline");
          if (!holder) {
            holder = document.createElement("div");
            holder.className = "ld-media-inline";
            mediaWrap.appendChild(holder);
          }
          holder.innerHTML = svg;

          // спрятать штатную миниатюру IMG, оставить одну превью
          const imgWrap = mediaWrap.querySelector(".image-wrap");
          if (imgWrap) imgWrap.classList.add("ld-hide");
        })
        .catch(() => {});
    }

    // если уже выбрано svg — показать
    const currentImg = mediaWrap.querySelector(".image-wrap img");
    if (currentImg && /\.svg(\?|#|$)/i.test(currentImg.src)) {
      renderInline(currentImg.src);
    }

    // слушаем клики по полю (Select/Remove)
    on(mediaWrap, "click", () => {
      setTimeout(() => {
        const img = mediaWrap.querySelector(".image-wrap img");
        const holder = mediaWrap.querySelector(".ld-media-inline");

        if (img && /\.svg(\?|#|$)/i.test(img.src)) {
          renderInline(img.src);
        } else {
          // не SVG или очищено — вернуть штатный IMG, прибрать inline
          mediaWrap.querySelector(".image-wrap")?.classList.remove("ld-hide");
          holder && holder.remove();
        }
      }, 60);
    });
  }

  function init(scope) {
    // простой select2 на нужных селектах
    $$('.acf-field[data-name="menu_icon"] select', scope).forEach(
      initPlainSelect2,
    );
    $$('.acf-field[data-name="post_icon_name"] select', scope).forEach(
      initPlainSelect2,
    );
    $$('.acf-field[data-name="term_icon_name"] select', scope).forEach(
      initPlainSelect2,
    );

    wireSourceRadios(scope);
    enhanceMediaSvgPreview(scope);
  }

  // ACF хуки + DOM ready
  if (window.acf && acf.addAction) {
    acf.addAction("ready", init);
    acf.addAction("append", init);
  } else {
    if (document.readyState !== "loading") init();
    else document.addEventListener("DOMContentLoaded", () => init());
  }
})();
