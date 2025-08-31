// =======================
// Dark/Light Theme Detection
// =======================
(function detectTheme() {
  const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
  const savedTheme = localStorage.getItem('theme');

  if (savedTheme) {
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
  } else if (prefersDarkScheme.matches) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
  } else {
    document.documentElement.setAttribute('data-bs-theme', 'light');
  }

  prefersDarkScheme.addEventListener('change', function (e) {
    const newTheme = e.matches ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', newTheme);
  });
})();
