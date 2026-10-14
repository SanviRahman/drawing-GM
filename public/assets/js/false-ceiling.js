/* Progressive-enhancement entrance transitions only on /false-ceiling. */
(() => {
  'use strict';
  if (!document.querySelector('.false-ceiling-page')) return;
  if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const selectors = '.fc-benefit,.fc-contact-panel,.fc-scope-card,.fc-price-card';
  const items = Array.from(document.querySelectorAll(selectors));
  if (!items.length) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fc-in-view');
        observer.unobserve(entry.target);
      }
    });
  }, { rootMargin: '0px 0px -35px 0px', threshold: 0.07 });
  items.forEach((item, index) => {
    item.classList.add('fc-animate');
    item.style.setProperty('--fc-delay', `${Math.min(index % 3, 2) * 90}ms`);
    observer.observe(item);
  });
})();
