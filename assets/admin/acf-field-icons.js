(function ($) {
  // LD_FIELD_ICON_CATALOG = { group: { mode:'sprite'|'inline', url:'...', map:{slug:symbol} } }

  function spriteSvg(group, slug) {
    var conf =
      (window.LD_FIELD_ICON_CATALOG && LD_FIELD_ICON_CATALOG[group]) || null;
    if (!conf) return "";
    var symbol = conf.map && conf.map[slug] ? conf.map[slug] : slug;
    return (
      '<svg class="ld-field-icon" aria-hidden="true"><use href="#' +
      symbol +
      '"></use></svg>'
    );
  }

  function inlineSvg(group, slug) {
    var conf =
      (window.LD_FIELD_ICON_CATALOG && LD_FIELD_ICON_CATALOG[group]) || null;
    if (!conf || !conf.url) return Promise.resolve("");
    var url = conf.url.replace(/\/$/, "") + "/" + slug + ".svg";
    return fetch(url)
      .then((r) => (r.ok ? r.text() : ""))
      .then((txt) =>
        txt
          ? txt
              .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
              .replace(/\sfill="[^"]*"/gi, "")
              .replace(
                /<svg\b([^>]*)>/i,
                '<svg$1 class="ld-field-icon" fill="currentColor">',
              )
          : "",
      )
      .catch(() => "");
  }

  function placeIcon($wrap, value) {
    // value format: group:slug
    var parts = (value || "").split(":");
    if (parts.length !== 2) return;
    var group = parts[0],
      slug = parts[1];

    var $label = $wrap.find("> .acf-label label");
    if (!$label.length || $label.find(".ld-field-icon").length) return;

    var conf = LD_FIELD_ICON_CATALOG[group] || {};
    if (conf.mode === "sprite") {
      $label.prepend(spriteSvg(group, slug));
    } else {
      inlineSvg(group, slug).then((svg) => {
        if (svg) $label.prepend(svg);
      });
    }
  }

  function scan() {
    $(".acf-field[data-ld-field-icon]").each(function () {
      var value = $(this).attr("data-ld-field-icon"); // group:slug
      if (value) placeIcon($(this), value);
    });
  }

  if (document.readyState !== "loading") scan();
  else document.addEventListener("DOMContentLoaded", scan);

  if (window.acf && acf.addAction) {
    acf.addAction("append", scan);
    acf.addAction("show_field", scan);
  }
})(jQuery);
