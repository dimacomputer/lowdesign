// =======================
// Dark/Light Theme Detection (Bootstrap + LowDesign)
// =======================
(function detectTheme() {
  const mq = window.matchMedia("(prefers-color-scheme: dark)");
  const saved = localStorage.getItem("theme"); // expected: "light" | "dark" | "auto"

  function apply(bsTheme) {
    const ldTheme = (bsTheme === "dark") ? "dark" : "default";
    document.documentElement.setAttribute("data-bs-theme", bsTheme);
    document.documentElement.setAttribute("data-ld-theme", ldTheme);
  }

  if (saved === "dark" || saved === "light") {
    apply(saved);
    return;
  }

  // auto / empty
  apply(mq.matches ? "dark" : "light");

  // live updates
  mq.addEventListener?.("change", (e) => {
    const savedNow = localStorage.getItem("theme");
    if (savedNow === "dark" || savedNow === "light") return;
    apply(e.matches ? "dark" : "light");
  });
})();
