// @ld-tags: core, header, nav, drawer

// Mobile drawer
const drawer = document.getElementById('mobile-drawer');
const navToggle = document.querySelector('.nav-toggle');
const drawerClose = drawer?.querySelector('.drawer-close');

function drawerOpen(){ if(!drawer) return; drawer.hidden=false; drawer.setAttribute('aria-hidden','false'); navToggle?.setAttribute('aria-expanded','true'); document.documentElement.classList.add('no-scroll'); }
function drawerHide(){ if(!drawer) return; drawer.hidden=true; drawer.setAttribute('aria-hidden','true'); navToggle?.setAttribute('aria-expanded','false'); document.documentElement.classList.remove('no-scroll'); }
navToggle?.addEventListener('click', () => drawer?.hidden ? drawerOpen() : drawerHide());
drawerClose?.addEventListener('click', drawerHide);
drawer?.addEventListener('click', (e)=>{ if(e.target===drawer) drawerHide(); });

// Search panel
const searchToggle = document.querySelector('.search-toggle');
const searchPanel  = document.getElementById('site-search');
searchToggle?.addEventListener('click', () => {
  const expanded = searchToggle.getAttribute('aria-expanded') === 'true';
  searchToggle.setAttribute('aria-expanded', String(!expanded));
  if (searchPanel){ searchPanel.hidden = expanded; if(!expanded){ searchPanel.querySelector('input')?.focus(); } }
});

// Sticky header show/hide on scroll
let lastY = window.scrollY; const header = document.getElementById('site-header');
window.addEventListener('scroll', () => {
  const y = window.scrollY;
  if (!header) return;
  if (y > 120 && y > lastY) header.classList.add('hide'); else header.classList.remove('hide');
  header.classList.toggle('scrolled', y>10);
  lastY = y;
});