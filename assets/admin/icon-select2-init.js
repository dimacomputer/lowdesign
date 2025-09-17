(function ($) {
  function init() {
    if (!$.fn.select2) return; // если Select2 нет — тихо выходим

    // sprite-селекты в наших контролах
    var selectors = [
      '.acf-field[data-name="post_icon_name"] select',
      '.acf-field[data-name="term_icon_name"] select',
      '.acf-field[data-name="menu_icon"] select',
      // вдруг где-то переиспользуешь:
      ".ld-theme-select select",
    ].join(",");

    $(selectors).each(function () {
      var $s = $(this);
      if ($s.data("select2")) return;
      $s.select2({
        width: "style", // уважает ширину из стилей/DOM
        minimumResultsForSearch: 10, // поиск появляется при 10+
        dropdownAutoWidth: true,
      });
    });
  }

  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);

  // если DOM меняется динамически (маловероятно на этих экранах) — можно дернуть ещё раз:
  window.ldInitIconSelect2 = init;
})(jQuery);
