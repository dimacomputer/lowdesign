// FILE: assets/admin/icon-preview.js
(function () {
  // ---------- utils ----------
  const $ = () => window.jQuery;
  const by = (sel, root = document) => root.querySelector(sel);
  const byAll = (sel, root = document) =>
    Array.from(root.querySelectorAll(sel));

  function ensurePreviewContainerFor(selectEl) {
    // ищем рядом контейнер .icon-preview, если нет — создаём слева от select
    const field = selectEl.closest(".acf-field");
    if (!field) return null;

    let box = field.querySelector(".icon-preview");
    if (!box) {
      box = document.createElement("span");
      box.className = "icon-preview";
      // пытаемся вставить перед селектом; если обёртка — в её начало
      const wrap = selectEl.parentElement || field;
      wrap.insertBefore(box, selectEl);
    }
    return box;
  }

  function renderSpritePreview(box, id) {
    if (!box) return;
    if (!id) {
      box.innerHTML = "";
      return;
    }
    box.innerHTML = `<svg class="icon icon--24" aria-hidden="true"><use href="#${id}"></use></svg>`;
  }

  function initPlainSelect2(selectEl) {
    const jq = $();
    if (!jq || !jq.fn || !jq.fn.select2) return; // Select2 не загружен — выходим

    const $el = jq(selectEl);
    if ($el.data("select2")) {
      $el.select2("destroy");
    }
    $el.select2({ width: "100%" });
  }

  function wireThemeSelect(select) {
    if (!select) return;
    initPlainSelect2(select);

    const box = ensurePreviewContainerFor(select);
    const apply = () => renderSpritePreview(box, select.value || "");

    // начальная отрисовка
    apply();

    // обновление по выбору
    select.addEventListener("change", apply);
  }

  function wireSourceRadios() {
    const wrapTheme = by('[data-ld="icon-theme-wrap"]');
    const wrapMedia = by('[data-ld="icon-media-wrap"]');
    const radios = byAll(
      '.acf-field[data-name="content_icon_source"] input[type="radio"]',
    );

    const apply = () => {
      const val = (radios.find((r) => r.checked) || {}).value || "none";
      if (wrapTheme) wrapTheme.classList.toggle("ld-hidden", val !== "sprite");
      if (wrapMedia) wrapMedia.classList.toggle("ld-hidden", val !== "media");
    };

    radios.forEach((r) => r.addEventListener("change", apply));
    apply();
  }

  // инлайн-превью SVG из медиа (ACF image)
  function enhanceMediaSvgPreview() {
    const mediaWrap = by('[data-ld="icon-media-wrap"] .acf-image-uploader');
    if (!mediaWrap) return;

    const renderInline = (url) => {
      fetch(url)
        .then((r) => r.text())
        .then((txt) => {
          // вырезаем скрипты, убираем явные fill, принудительно ставим currentColor
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

          // скрываем стандартный <img> превью ACF, чтобы не было «двойника»
          mediaWrap.querySelector(".image-wrap")?.classList.add("ld-hidden");
        })
        .catch(() => {});
    };

    // первичная отрисовка — если уже выбран SVG
    const currentImg = mediaWrap.querySelector(".image-wrap img");
    if (currentImg && /\.svg(\?|#|$)/i.test(currentImg.src)) {
      renderInline(currentImg.src);
    }

    // отслеживаем клики внутри аплоада (Select / Remove), ACF обновит .image-wrap
    document.addEventListener("click", (e) => {
      if (!mediaWrap.contains(e.target)) return;
      setTimeout(() => {
        const img = mediaWrap.querySelector(".image-wrap img");
        const holder = mediaWrap.querySelector(".ld-media-inline");
        if (img && /\.svg(\?|#|$)/i.test(img.src)) {
          renderInline(img.src);
        } else {
          mediaWrap.querySelector(".image-wrap")?.classList.remove("ld-hidden");
          holder?.remove();
        }
      }, 50);
    });
  }

  function init() {
    // 1) обычный Select2 + внешний превью-блок для sprite-иконок
    byAll(
      '.acf-field[data-name="menu_icon"] select,' +
        '.acf-field[data-name="post_icon_name"] select,' +
        '.acf-field[data-name="term_icon_name"] select',
    ).forEach(wireThemeSelect);

    // 2) радиокнопки источника
    wireSourceRadios();

    // 3) медиа-превью SVG (inline, currentColor)
    enhanceMediaSvgPreview();
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);
})();
