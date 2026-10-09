/* Public homepage interactions. Bootstrap handles collapse, dropdown, accordion and carousel. */
(() => {
  'use strict';

  const floatingButton = document.querySelector('[data-contact-toggle]');
  const floatingPanel = document.getElementById('contactPanel');
  if (floatingButton && floatingPanel) {
    const toggle = (state) => {
      floatingPanel.hidden = !state;
      floatingButton.setAttribute('aria-expanded', String(state));
      floatingButton.classList.toggle('is-open', state);
    };
    floatingButton.addEventListener('click', () => toggle(floatingPanel.hidden));
    document.querySelector('[data-contact-close]')?.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !floatingPanel.hidden) { toggle(false); floatingButton.focus(); }
    });
    document.addEventListener('click', (e) => {
      if (!floatingPanel.hidden && !floatingPanel.contains(e.target) && !floatingButton.contains(e.target)) toggle(false);
    });
  }

  const backToTop = document.getElementById('backToTop');
  if (backToTop) {
    const update = () => { backToTop.hidden = window.scrollY < 350; };
    update();
    window.addEventListener('scroll', update, { passive: true });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // Keep both image layers the same intrinsic width while the before-image is clipped.
  document.querySelectorAll('[data-comparison]').forEach((holder) => {
    const slider = holder.querySelector('input[type="range"]');
    const updateSize = () => holder.style.setProperty('--full-width', `${holder.getBoundingClientRect().width}px`);
    const updateSplit = () => holder.style.setProperty('--split', `${slider?.value ?? 50}%`);
    updateSize(); updateSplit();
    slider?.addEventListener('input', updateSplit);
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(updateSize).observe(holder);
    else window.addEventListener('resize', updateSize, { passive: true });
  });

  // Lazy-play validated, supported embed providers only after a deliberate click.
  document.querySelectorAll('[data-video-load]').forEach((button) => {
    button.addEventListener('click', () => {
      try {
        const url = new URL(button.dataset.videoUrl || '');
        const allow = ['www.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com', 'player.vimeo.com'];
        if (url.protocol !== 'https:' || !allow.includes(url.hostname)) return;
        url.searchParams.set('autoplay', '1');
        const iframe = document.createElement('iframe');
        iframe.src = url.toString();
        iframe.title = button.dataset.videoTitle || 'Project video';
        iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        iframe.allowFullscreen = true;
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        iframe.setAttribute('loading', 'eager');
        button.parentNode.replaceChildren(iframe);
      } catch (error) { /* Keep existing preview if URL is not valid. */ }
    });
  });

  // All visible review, YouTube and gallery cards share the same accessible carousel.
  // Scroll-snap + scrollBy keep mobile swipes native and don't interfere with media players.
  document.querySelectorAll('[data-hp-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-hp-track]');
    if (!track) return;
    const slides = [...track.querySelectorAll('.hp-slider-slide')];
    const previous = carousel.querySelector('[data-hp-prev]');
    const next = carousel.querySelector('[data-hp-next]');
    const counter = carousel.querySelector('[data-hp-position]');
    if (!slides.length) return;

    const visibleCount = () => {
      const width = slides[0].getBoundingClientRect().width;
      const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
      return Math.max(1, Math.round((track.clientWidth + gap) / (width + gap)));
    };
    const currentIndex = () => {
      const firstLeft = slides[0].offsetLeft;
      const scrollPoint = track.scrollLeft + firstLeft;
      let nearest = 0;
      let distance = Infinity;
      slides.forEach((slide, index) => {
        const delta = Math.abs(slide.offsetLeft - scrollPoint);
        if (delta < distance) { distance = delta; nearest = index; }
      });
      return nearest;
    };
    const update = () => {
      const start = currentIndex();
      const itemsVisible = visibleCount();
      const atStart = track.scrollLeft <= 2;
      const atEnd = track.scrollWidth - track.clientWidth - track.scrollLeft <= 3;
      if (previous) previous.disabled = atStart;
      if (next) next.disabled = atEnd;
      if (counter) {
        counter.textContent = `${Math.min(slides.length, start + 1)}–${Math.min(slides.length, start + itemsVisible)} / ${slides.length}`;
      }
    };
    const scrollOne = (direction) => {
      const i = currentIndex();
      const to = Math.max(0, Math.min(slides.length - 1, i + direction));
      if (to === i) return;
      track.scrollTo({ left: slides[to].offsetLeft - slides[0].offsetLeft, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    };
    previous?.addEventListener('click', () => scrollOne(-1));
    next?.addEventListener('click', () => scrollOne(1));
    track.addEventListener('keydown', (event) => {
      if (event.target !== track) return; // Keep keyboard controls on videos & comparison sliders.
      if (event.key === 'ArrowLeft') { event.preventDefault(); scrollOne(-1); }
      if (event.key === 'ArrowRight') { event.preventDefault(); scrollOne(1); }
      if (event.key === 'Home') { event.preventDefault(); track.scrollTo({ left: 0, behavior: 'smooth' }); }
      if (event.key === 'End') { event.preventDefault(); track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' }); }
    });
    let queued = false;
    const scheduleUpdate = () => {
      if (queued) return;
      queued = true;
      requestAnimationFrame(() => { queued = false; update(); });
    };
    track.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate, { passive: true });
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(scheduleUpdate).observe(track);
    // Let layout, images, and local fonts finish determining accurate slide widths.
    requestAnimationFrame(scheduleUpdate);
    window.addEventListener('load', scheduleUpdate, { once: true });
  });

  // Prices are strictly taken from active package/item records, never from demo numbers.
  const calculator = document.querySelector('.hp-calculator-panel');
  if (calculator) {
    const packages = [...calculator.querySelectorAll('input[name="hp_package"]')];
    const priceSelect = calculator.querySelector('[data-price-select]');
    const result = calculator.querySelector('[data-estimate-amount]');
    const description = calculator.querySelector('[data-estimate-explainer]');
    const addonRows = [...calculator.querySelectorAll('[data-addon-row]')];
    const addons = [...calculator.querySelectorAll('[data-calc-addon]')];
    const emptyAddons = calculator.querySelector('[data-addon-empty]');
    const currentPackage = () => packages.find((input) => input.checked)?.value || '';
    const formatMoney = (number, currency) => {
      if (!Number.isFinite(number)) return 'Price on request';
      if (!currency || !/^[A-Z]{3}$/.test(currency)) return number.toFixed(2);
      try { return new Intl.NumberFormat('en-SG', { style: 'currency', currency, maximumFractionDigits: 2 }).format(number); }
      catch { return `${currency} ${number.toFixed(2)}`; }
    };
    const render = () => {
      if (!priceSelect || !result || !description) return;
      const option = priceSelect.selectedOptions[0];
      if (!option?.value) {
        result.textContent = 'Choose an option';
        description.textContent = 'Select a published price item to view the guide price.';
        return;
      }
      const base = Number(option.dataset.amount);
      const upper = option.dataset.max ? Number(option.dataset.max) : null;
      const kind = option.dataset.priceType || 'call';
      const currency = option.dataset.currency || '';
      const selected = addons.filter((input) => !input.closest('[hidden]') && input.checked && input.dataset.package === currentPackage());
      const canSum = (kind === 'fixed' || kind === 'from') && Number.isFinite(base) && option.dataset.amount !== '';
      const addOnSafe = selected.every((input) => input.dataset.priceType === 'fixed'
        && input.dataset.amount !== '' && Number.isFinite(Number(input.dataset.amount))
        && ['', 'job', 'project', 'flat'].includes((input.dataset.unit || '').toLowerCase()));
      const addOnTotal = selected.reduce((total, input) => total + Number(input.dataset.amount || 0), 0);
      if (canSum && addOnSafe) {
        const number = base + addOnTotal;
        result.textContent = kind === 'from' ? `From ${formatMoney(number, currency)}` : formatMoney(number, currency);
        description.textContent = selected.length ? 'Includes the selected fixed-price add-ons. Confirm availability and scope.' : 'Published guide price. Confirm scope and site conditions before proceeding.';
      } else if (kind === 'range' && option.dataset.amount !== '' && upper !== null && Number.isFinite(upper)) {
        result.textContent = `${formatMoney(base, currency)} – ${formatMoney(upper, currency)}`;
        description.textContent = selected.length ? 'Range shown excludes add-ons needing a separate quotation.' : 'Published price range. Final cost depends on site conditions.';
      } else {
        result.textContent = option.dataset.display || 'Price on request';
        description.textContent = selected.length ? 'Selected add-ons require separate confirmation.' : 'Contact our team for a tailored quotation.';
      }
    };
    const filterPackage = () => {
      const selected = currentPackage();
      [...(priceSelect?.options || [])].forEach((option, index) => {
        if (index === 0) return;
        option.hidden = option.dataset.package !== selected;
        option.disabled = option.hidden;
      });
      if (priceSelect) priceSelect.value = '';
      let available = 0;
      addonRows.forEach((row) => {
        row.hidden = row.dataset.package !== selected;
        if (!row.hidden) available++;
        const input = row.querySelector('input');
        if (input && row.hidden) input.checked = false;
      });
      if (emptyAddons) emptyAddons.hidden = available > 0;
      render();
    };
    packages.forEach((input) => input.addEventListener('change', filterPackage));
    priceSelect?.addEventListener('change', render);
    addons.forEach((input) => input.addEventListener('change', render));
    filterPackage();
  }

  // Show the validation result after returning from the POST redirect.
  const quoteFeedback = document.querySelector('#quote-form .alert');
  if (quoteFeedback) quoteFeedback.scrollIntoView({ block: 'center', behavior: 'auto' });
})();
