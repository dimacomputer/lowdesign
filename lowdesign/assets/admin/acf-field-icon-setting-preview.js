(function ($) {
  function renderFromValue(value) {
    if (!value || value.indexOf(":") === -1) return Promise.resolve("");
    var parts = value.split(":");
    var group = parts[0],
      slug = parts[1];
    var conf =
      (window.LD_FIELD_ICON_CATALOG && LD_FIELD_ICON_CATALOG[group]) || null;
    if (!conf) return Promise.resolve("");

    if (conf.mode === "sprite") {
      var symbol = conf.map && conf.map[slug] ? conf.map[slug] : slug;
      return Promise.resolve(
        '<svg aria-hidden="true"><use href="#' + symbol + '"></use></svg>',
      );
    } else {
      var url = (conf.url || "").replace(/\/$/, "") + "/" + slug + ".svg";
      if (!url) return Promise.resolve("");
      return fetch(url)
        .then((r) => (r.ok ? r.text() : ""))
        .then((txt) =>
          txt
            ? txt
                .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
                .replace(/\sfill="[^"]*"/gi, "")
                .replace(/<svg\b([^>]*)>/i, '<svg$1 fill="currentColor">')
            : "",
        )
        .catch(() => "");
    }
  }

  function attach(scope) {
    $(scope)
      .find(".acf-field-setting-ld_field_icon select")
      .each(function () {
        var $sel = $(this);
        var $holder = $sel.next(".ld-icon-preview");
        if (!$holder.length) {
          $holder = $('<span class="ld-icon-preview"></span>');
          $sel.after($holder);
        }
        var update = function () {
          renderFromValue($sel.val()).then(function (svg) {
            if (svg) {
              $holder.html(svg).removeClass("ld-hidden");
            } else {
              $holder.empty().addClass("ld-hidden");
            }
          });
        };
        $sel.on("change", update);
        update();
      });
  }

  if (document.readyState !== "loading") attach(document);
  else
    document.addEventListener("DOMContentLoaded", function () {
      attach(document);
    });

  if (window.acf && acf.addAction) {
    acf.addAction("append", attach);
    acf.addAction("show_field", attach);
  }
})(jQuery);
