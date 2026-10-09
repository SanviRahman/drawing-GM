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

  // Database-driven pricing calculator. Prices come from Admin > Pricing Items/Add-ons.
  const calculator = document.querySelector('.hp-calculator-panel');
  if (calculator) {
    const packageSelect = calculator.querySelector('[data-calc-package]');
    const gradeRows = [...calculator.querySelectorAll('[data-grade-row]')];
    const grades = [...calculator.querySelectorAll('[data-calc-grade]')];
    const addonRows = [...calculator.querySelectorAll('[data-addon-row]')];
    const addons = [...calculator.querySelectorAll('[data-calc-addon]')];
    const estimate = calculator.querySelector('[data-estimate-amount]');
    const explanation = calculator.querySelector('[data-estimate-explainer]');
    const breakdown = calculator.querySelector('[data-estimate-breakdown]');
    const emptyAddons = calculator.querySelector('[data-addon-empty]');
    const quantityWrap = calculator.querySelector('[data-quantity-wrap]');
    const quantityOut = calculator.querySelector('[data-qty-value]');
    let quantity = 1;

    const compatibleUnits = new Set(['door', 'doors', 'frame', 'frames', 'piece', 'pieces', 'unit', 'units', 'set', 'sets']);
    const money = (value, currency) => {
      if (!Number.isFinite(value)) return 'Price on request';
      try {
        return new Intl.NumberFormat('en-SG', {
          style: 'currency',
          currency: /^[A-Z]{3}$/.test(currency) ? currency : 'SGD',
          maximumFractionDigits: 2,
        }).format(value);
      } catch {
        return `${currency || 'SGD'} ${value.toFixed(2)}`;
      }
    };
    const numberOrNull = (value) => value === '' || value == null || !Number.isFinite(Number(value)) ? null : Number(value);
    const selectedGrade = () => grades.find((input) => input.checked && !input.closest('[hidden]'));

    const priceBand = (kind, amount, maximum, multiplier = 1) => {
      const min = numberOrNull(amount);
      const max = numberOrNull(maximum);
      if (kind === 'call' || min === null) return { quote: true, min: 0, max: 0, from: false, range: false };
      if (kind === 'range') return { quote: max === null, min: min * multiplier, max: (max ?? min) * multiplier, from: false, range: true };
      if (kind === 'from') return { quote: false, min: min * multiplier, max: min * multiplier, from: true, range: false };
      return { quote: false, min: min * multiplier, max: min * multiplier, from: false, range: false };
    };

    const renderPrice = () => {
      const grade = selectedGrade();
      if (!estimate || !explanation) return;
      if (!grade) {
        estimate.textContent = 'Choose a paint grade';
        explanation.textContent = 'Select an available grade to see its published rate.';
        if (breakdown) { breakdown.hidden = true; breakdown.innerHTML = ''; }
        return;
      }

      const currency = grade.dataset.currency || 'SGD';
      const gradeUnit = (grade.dataset.unit || '').toLowerCase();
      const gradeMultiplier = compatibleUnits.has(gradeUnit) ? quantity : 1;
      const base = priceBand(grade.dataset.priceType || 'call', grade.dataset.amount, grade.dataset.max, gradeMultiplier);
      const selectedAddons = addons.filter((input) => input.checked && !input.closest('[hidden]'));

      let minTotal = base.min;
      let maxTotal = base.max;
      let needsQuote = base.quote;
      let hasFrom = base.from;
      let hasRange = base.range;
      const lines = [];

      const baseLabel = grade.closest('.hp-calc-grade')?.querySelector('strong')?.textContent?.trim() || 'Base painting price';
      const baseDisplay = grade.dataset.display || 'Call for Price';
      lines.push(`<div><span>${baseLabel}</span><strong>${baseDisplay}</strong></div>`);

      selectedAddons.forEach((input) => {
        const band = priceBand(input.dataset.priceType || 'call', input.dataset.amount, input.dataset.max, 1);
        minTotal += band.min;
        maxTotal += band.max;
        needsQuote = needsQuote || band.quote;
        hasFrom = hasFrom || band.from;
        hasRange = hasRange || band.range;
        const label = input.dataset.name || 'Add-on';
        const display = input.dataset.display || 'Call for Price';
        lines.push(`<div><span>+ ${label}</span><strong>${display}</strong></div>`);
      });

      const hasKnownAmount = minTotal > 0 || maxTotal > 0;

      if (needsQuote && hasKnownAmount) {
        // Do not hide known CMS amounts just because another selected component is quote-only.
        // Example: base painting = Call for Price, selected add-on = SGD 150–350.
        // Show the known amount and explain that quote-only components are excluded.
        if (hasRange || Math.abs(maxTotal - minTotal) > 0.009) {
          estimate.textContent = `${money(minTotal, currency)} – ${money(maxTotal, currency)}`;
        } else if (hasFrom) {
          estimate.textContent = `From ${money(minTotal, currency)}`;
        } else {
          estimate.textContent = money(minTotal, currency);
        }
        explanation.textContent = 'Known priced selections are shown here. Any Call for Price item is excluded until its amount is saved in Admin.';
      } else if (needsQuote) {
        estimate.textContent = 'Call for Price';
        explanation.textContent = 'The selected pricing item has no numeric amount saved in Admin yet.';
      } else if (hasRange || Math.abs(maxTotal - minTotal) > 0.009) {
        estimate.textContent = `${money(minTotal, currency)} – ${money(maxTotal, currency)}`;
        explanation.textContent = 'Calculated from the active Pricing Item and selected Add-ons saved in Admin.';
      } else if (hasFrom) {
        estimate.textContent = `From ${money(minTotal, currency)}`;
        explanation.textContent = 'Calculated minimum from the active Pricing Item and selected Add-ons saved in Admin.';
      } else {
        estimate.textContent = money(minTotal, currency);
        explanation.textContent = 'Calculated from the active Pricing Item and selected Add-ons saved in Admin.';
      }

      if (breakdown) {
        breakdown.innerHTML = lines.join('');
        breakdown.hidden = false;
      }
    };

    const updateQuantityVisibility = () => {
      const grade = selectedGrade();
      const unit = (grade?.dataset.unit || '').toLowerCase();
      if (quantityWrap) quantityWrap.hidden = !compatibleUnits.has(unit);
    };

    const filterPackage = () => {
      if (!packageSelect) return;
      const id = packageSelect.value;
      gradeRows.forEach((row) => { row.hidden = row.dataset.package !== id; });
      const available = grades.filter((g) => g.dataset.package === id);
      grades.forEach((g) => { g.checked = false; });
      if (available.length) available[0].checked = true;

      let activeAddons = 0;
      addonRows.forEach((row) => {
        row.hidden = row.dataset.package !== id;
        if (!row.hidden) activeAddons += 1;
        const input = row.querySelector('input');
        if (input) input.checked = false;
      });
      if (emptyAddons) emptyAddons.hidden = activeAddons > 0;
      quantity = 1;
      if (quantityOut) quantityOut.textContent = '1';
      updateQuantityVisibility();
      renderPrice();
    };

    packageSelect?.addEventListener('change', filterPackage);
    grades.forEach((g) => g.addEventListener('change', () => {
      quantity = 1;
      if (quantityOut) quantityOut.textContent = '1';
      updateQuantityVisibility();
      renderPrice();
    }));
    addons.forEach((a) => a.addEventListener('change', renderPrice));
    calculator.querySelector('[data-qty-minus]')?.addEventListener('click', () => {
      quantity = Math.max(1, quantity - 1);
      if (quantityOut) quantityOut.textContent = String(quantity);
      renderPrice();
    });
    calculator.querySelector('[data-qty-plus]')?.addEventListener('click', () => {
      quantity = Math.min(99, quantity + 1);
      if (quantityOut) quantityOut.textContent = String(quantity);
      renderPrice();
    });
    filterPackage();
  }

  // Show the validation result after returning from the POST redirect.
  const quoteFeedback = document.querySelector('#quote-form .alert');
  if (quoteFeedback) quoteFeedback.scrollIntoView({ block: 'center', behavior: 'auto' });
})();


/* ========================================================================
   2026-10-09 • Homepage motion / UX enhancement layer
   Progressive enhancement only: CMS rendering and Bootstrap behavior remain intact.
   ======================================================================== */
(() => {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1) Page loader. Created in JS so no Blade markup is required.
  if (!reduceMotion && document.body) {
    const loader = document.createElement('div');
    loader.className = 'hp-page-loader';
    loader.setAttribute('role', 'status');
    loader.setAttribute('aria-live', 'polite');
    loader.innerHTML = '<div class="hp-loader-inner"><span class="hp-loader-spinner" aria-hidden="true"></span><span>Loading...</span></div>';
    document.body.prepend(loader);
    const hideLoader = () => {
      loader.classList.add('is-hidden');
      window.setTimeout(() => loader.remove(), 720);
    };
    if (document.readyState === 'complete') window.setTimeout(hideLoader, 180);
    else window.addEventListener('load', () => window.setTimeout(hideLoader, 180), { once: true });
    window.setTimeout(hideLoader, 4500); // failsafe if an external asset stalls
  }

  // 2) Sticky header transition.
  const header = document.querySelector('.site-header.site-header-sticky');
  if (header) {
    const setHeaderState = () => header.classList.toggle('is-scrolled', window.scrollY > 20);
    setHeaderState();
    window.addEventListener('scroll', setHeaderState, { passive: true });
  }

  // Replay the hero trust entrance on every Bootstrap carousel slide.
  const heroCarousel = document.querySelector('.hp-hero-carousel');
  const heroTrust = document.querySelector('.hp-hero-trust');
  if (!reduceMotion && heroCarousel && heroTrust) {
    heroCarousel.addEventListener('slide.bs.carousel', () => {
      heroTrust.classList.remove('hp-hero-replay');
      void heroTrust.offsetWidth;
      heroTrust.classList.add('hp-hero-replay');
    });
  }

  // 3) Scroll reveal / stagger. Works with current Home markup; no data changes required.
  const revealSelectors = [
    '.hp-intro .hp-intro-title', '.hp-intro .hp-intro-lead', '.hp-intro .hp-intro-benefit', '.hp-intro .hp-quote-path',
    '.hp-benefit-grid .hp-benefit-card', '.hp-guarantee .hp-guarantee-item', '.hp-guarantee .hp-guarantee-photo',
    '.hp-warning .hp-section-heading', '.hp-warning .hp-warning-box', '.hp-warning .hp-warning-bottom',
    '.hp-videos .hp-section-heading', '.hp-videos .hp-slider-slide',
    '.hp-reviews .hp-section-heading', '.hp-reviews .hp-slider-slide',
    '.hp-calculator-section .hp-section-heading', '.hp-calculator-panel',
    '.hp-process .hp-section-heading', '.hp-process .hp-process-card',
    '.hp-gallery .hp-section-heading', '.hp-gallery .hp-slider-slide',
    '.hp-chat-section .hp-section-heading', '.hp-chat-section .hp-slider-slide',
    '.hp-recap .hp-section-heading', '.hp-recap .hp-recap-panel',
    '.hp-faq-section .hp-section-heading', '.hp-faq-section .accordion-item',
    '.hp-quote-section .hp-quote-copy', '.hp-quote-section .hp-quote-form-wrap',
    '.hp-regional-heading', '.hp-regional-card', '.hp-custom-card', '.hp-custom-media'
  ];

  const revealNodes = [...new Set(revealSelectors.flatMap((selector) => [...document.querySelectorAll(selector)]))];
  revealNodes.forEach((node, index) => {
    node.classList.add('hp-reveal');
    if (node.matches('.hp-quote-form-wrap,.hp-guarantee-photo,.hp-custom-media')) node.classList.add('hp-reveal-right');
    if (node.matches('.hp-quote-copy')) node.classList.add('hp-reveal-left');
    if (node.matches('.hp-slider-slide,.hp-process-card,.hp-intro-benefit,.hp-guarantee-item,.accordion-item,.hp-regional-card')) {
      node.classList.add('hp-stagger-item');
      node.style.setProperty('--hp-stagger-delay', `${Math.min((index % 6) * 95, 475)}ms`);
    }
  });

  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealNodes.forEach((node) => node.classList.add('is-visible'));
  } else {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.10, rootMargin: '0px 0px -4% 0px' });
    revealNodes.forEach((node) => revealObserver.observe(node));
  }

  // 4) Auto-slide + mouse/touch drag for current horizontal carousels.
  document.querySelectorAll('[data-hp-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-hp-track]');
    if (!track) return;
    const slides = [...track.querySelectorAll('.hp-slider-slide')];
    if (slides.length < 2) return;

    let dragging = false;
    let pointerId = null;
    let startX = 0;
    let startScroll = 0;
    let paused = false;
    let timer = null;

    const isInteractive = (target) => target.closest('a,button,input,select,textarea,iframe,video,[data-comparison]');
    const slideWidth = () => {
      const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
      return (slides[0]?.getBoundingClientRect().width || track.clientWidth) + gap;
    };
    const atEnd = () => track.scrollWidth - track.clientWidth - track.scrollLeft < 8;
    const autoNext = () => {
      if (reduceMotion || paused || dragging || document.hidden) return;
      const left = atEnd() ? 0 : track.scrollLeft + slideWidth();
      track.scrollTo({ left, behavior: 'smooth' });
    };
    const startAuto = () => {
      if (reduceMotion) return;
      window.clearInterval(timer);
      timer = window.setInterval(autoNext, 6800);
    };

    track.addEventListener('pointerdown', (event) => {
      if (event.pointerType === 'mouse' && event.button !== 0) return;
      if (isInteractive(event.target)) return;
      dragging = true;
      pointerId = event.pointerId;
      startX = event.clientX;
      startScroll = track.scrollLeft;
      track.classList.add('is-dragging');
      track.setPointerCapture?.(event.pointerId);
      paused = true;
    });
    track.addEventListener('pointermove', (event) => {
      if (!dragging || event.pointerId !== pointerId) return;
      track.scrollLeft = startScroll - (event.clientX - startX);
    });
    const stopDrag = (event) => {
      if (!dragging || (event?.pointerId != null && event.pointerId !== pointerId)) return;
      dragging = false;
      track.classList.remove('is-dragging');
      try { if (pointerId != null && track.hasPointerCapture?.(pointerId)) track.releasePointerCapture(pointerId); } catch (_) {}
      pointerId = null;
      paused = false;
      startAuto();
    };
    track.addEventListener('pointerup', stopDrag);
    track.addEventListener('pointercancel', stopDrag);
    track.addEventListener('mouseenter', () => { paused = true; });
    track.addEventListener('mouseleave', () => { if (!dragging) { paused = false; startAuto(); } });
    carousel.addEventListener('focusin', () => { paused = true; });
    carousel.addEventListener('focusout', () => { paused = false; startAuto(); });
    document.addEventListener('visibilitychange', startAuto);
    startAuto();
  });

  // 5) Count-up support for experience/project metrics.
  const counters = [...document.querySelectorAll('[data-countup],.hp-countup,.hp-stat-number,.hp-experience-number')]
    .filter((el) => /-?\d[\d,.]*/.test(el.textContent || ''));

  const animateCounter = (el) => {
    if (el.dataset.countupDone === '1') return;
    const original = (el.textContent || '').trim();
    const match = original.match(/-?\d[\d,.]*/);
    if (!match) return;
    const raw = match[0];
    const target = Number(raw.replace(/,/g, ''));
    if (!Number.isFinite(target)) return;
    const prefix = original.slice(0, match.index);
    const suffix = original.slice((match.index || 0) + raw.length);
    const decimals = (raw.split('.')[1] || '').length;
    const duration = 1800;
    const start = performance.now();
    el.dataset.countupDone = '1';
    const tick = (now) => {
      const progress = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = target * eased;
      const formatted = current.toLocaleString('en-SG', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
      el.textContent = `${prefix}${formatted}${suffix}`;
      if (progress < 1) requestAnimationFrame(tick);
      else el.textContent = original;
    };
    requestAnimationFrame(tick);
  };

  if (reduceMotion || !('IntersectionObserver' in window)) counters.forEach(animateCounter);
  else {
    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.45 });
    counters.forEach((el) => counterObserver.observe(el));
  }
})();
