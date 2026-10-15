/* Public tracking runtime. Uses validated IDs/config from Laravel; never executes raw admin snippets. */
(() => {
  'use strict';

  const cfg = window.__websiteTracking || {};
  const providers = Array.isArray(cfg.providers) ? cfg.providers : [];
  const rules = Array.isArray(cfg.rules) ? cfg.rules : [];
  const page = cfg.page && typeof cfg.page === 'object' ? cfg.page : {};
  const marketingAllowed = cfg.marketingConsent === true || cfg.localTestOverride === true;
  const dataLayer = window.dataLayer = window.dataLayer || [];

  const clean = (value) => {
    if (value == null) return undefined;
    if (typeof value === 'string') {
      const trimmed = value.trim();
      return trimmed === '' ? undefined : trimmed.slice(0, 500);
    }
    if (typeof value === 'number' || typeof value === 'boolean') return value;
    if (Array.isArray(value)) return value.map(clean).filter((item) => item !== undefined).slice(0, 50);
    if (typeof value === 'object') {
      const output = {};
      Object.entries(value).slice(0, 50).forEach(([key, item]) => {
        const safe = clean(item);
        if (safe !== undefined) output[key] = safe;
      });
      return output;
    }
    return undefined;
  };

  const safeEventData = (data = {}) => {
    const blocked = new Set(['email', 'phone', 'telephone', 'message', 'name', 'full_name', 'contact_number', 'whatsapp_number']);
    const output = {};
    Object.entries(data || {}).forEach(([key, value]) => {
      if (blocked.has(String(key).toLowerCase())) return;
      const safe = clean(value);
      if (safe !== undefined) output[key] = safe;
    });
    return output;
  };

  const getPath = (source, path) => {
    if (typeof path !== 'string' || path.trim() === '') return undefined;
    return path.split('.').reduce((value, key) => value != null ? value[key] : undefined, source);
  };

  const mappedParams = (parameterMap, eventData) => {
    if (!parameterMap || typeof parameterMap !== 'object' || Array.isArray(parameterMap)) return safeEventData(eventData);
    const source = { event: eventData, page, site: { currency: 'SGD' } };
    const mapped = {};
    Object.entries(parameterMap).forEach(([target, sourcePath]) => {
      const value = typeof sourcePath === 'string' ? getPath(source, sourcePath) : sourcePath;
      const safe = clean(value);
      if (safe !== undefined) mapped[target] = safe;
    });
    return safeEventData({ ...safeEventData(eventData), ...mapped });
  };

  const providerEnabled = (provider) => providers.some((item) => item?.provider === provider);
  const configuredRules = (provider, internalEvent) => rules.filter((rule) => rule?.provider === provider && rule?.internal_event === internalEvent);

  const defaultProviderEvent = (provider, internalEvent) => {
    const maps = {
      meta_pixel: {
        page_view: 'PageView',
        view_content: 'ViewContent',
        view_pricing: 'ViewPricing',
        contact: 'Contact',
        click_whatsapp: 'ClickWhatsApp',
        click_email: 'ClickEmail',
        click_cta: 'ClickCTA',
        quote_form_start: 'QuoteFormStart',
        quote_form_attempt: 'QuoteFormAttempt',
        video_play: 'VideoPlay',
        video_complete: 'VideoComplete',
        click_call: 'ClickCall',
        submit_quote: 'SubmitQuote',
        lead: 'Lead',
        scroll_depth: 'ScrollDepth',
      },
      ga4: {
        page_view: 'page_view',
        view_content: 'view_item',
        view_pricing: 'view_pricing',
        contact: 'contact',
        click_whatsapp: 'click_whatsapp',
        click_email: 'click_email',
        click_cta: 'click_cta',
        quote_form_start: 'quote_form_start',
        quote_form_attempt: 'quote_form_attempt',
        video_play: 'video_start',
        video_complete: 'video_complete',
        click_call: 'click_call',
        submit_quote: 'submit_quote',
        lead: 'generate_lead',
        scroll_depth: 'scroll',
      },
      tiktok: {
        page_view: 'PageView',
        view_content: 'ViewContent',
        contact: 'Contact',
        submit_quote: 'SubmitForm',
        lead: 'SubmitForm',
      },
    };
    return maps[provider]?.[internalEvent] || null;
  };

  const metaStandardEvents = new Set([
    'PageView', 'ViewContent', 'Search', 'AddToCart', 'AddToWishlist', 'InitiateCheckout',
    'AddPaymentInfo', 'Purchase', 'Lead', 'CompleteRegistration', 'Contact', 'Schedule',
    'FindLocation', 'Donate', 'CustomizeProduct', 'StartTrial', 'SubmitApplication', 'Subscribe'
  ]);

  const dispatchToProvider = (provider, providerEvent, params) => {
    if (!providerEvent || !marketingAllowed || cfg.providerScriptsAllowed !== true) return;

    if (provider === 'meta_pixel' && typeof window.fbq === 'function') {
      // PageView is already emitted immediately after Pixel initialization in the layout.
      if (providerEvent === 'PageView' && cfg.metaPageViewFired === true) return;
      window.fbq(metaStandardEvents.has(providerEvent) ? 'track' : 'trackCustom', providerEvent, params);
      return;
    }

    if (provider === 'ga4' && typeof window.gtag === 'function') {
      window.gtag('event', providerEvent, params);
      return;
    }

    if (provider === 'tiktok' && window.ttq && typeof window.ttq.track === 'function') {
      window.ttq.track(providerEvent, params);
    }
  };

  const dispatchMappings = (internalEvent, eventData) => {
    ['meta_pixel', 'ga4', 'tiktok'].forEach((provider) => {
      if (!providerEnabled(provider)) return;
      const matching = configuredRules(provider, internalEvent);

      // An admin-created rule is authoritative. Disabled means do not dispatch.
      if (matching.length > 0) {
        matching.filter((rule) => rule?.is_enabled === true).forEach((rule) => {
          if (rule.requires_marketing_consent === true && !marketingAllowed) return;
          dispatchToProvider(provider, rule.provider_event, mappedParams(rule.parameter_map, eventData));
        });
        return;
      }

      // If no rule exists yet, use a safe built-in event mapping so a freshly-enabled
      // provider still works. Creating a disabled admin rule suppresses this fallback.
      const fallback = defaultProviderEvent(provider, internalEvent);
      if (fallback) dispatchToProvider(provider, fallback, safeEventData(eventData));
    });
  };

  window.trackEvent = function trackEvent(eventName, data = {}, options = {}) {
    const event = typeof eventName === 'string' ? eventName.trim() : '';
    if (!event) return;

    const payload = safeEventData(data);
    if (options.pushDataLayer !== false) {
      dataLayer.push({ event, ...payload });
    }
    if (options.dispatchProviders !== false) {
      dispatchMappings(event, payload);
    }
  };

  // Finish provider dispatch for the initial page view without duplicating the dataLayer event.
  dispatchMappings('page_view', page);

  // Standard GTM lifecycle visibility.
  // A real GTM container emits gtm.dom/gtm.load itself. When GTM is not configured,
  // emit diagnostics-only equivalents so DataLayer inspection keeps the same lifecycle shape.
  if (cfg.hasGtmContainer !== true) {
    const pushGtmDom = () => dataLayer.push({ event: 'gtm.dom', 'gtm.synthetic': true });
    const pushGtmLoad = () => dataLayer.push({ event: 'gtm.load', 'gtm.synthetic': true });

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', pushGtmDom, { once: true });
    } else {
      pushGtmDom();
    }

    if (document.readyState === 'complete') {
      pushGtmLoad();
    } else {
      window.addEventListener('load', pushGtmLoad, { once: true });
    }
  }

  // ViewContent: content/service/blog pages only; Home keeps PageView only.
  if (page.page_type && page.page_type !== 'home') {
    window.trackEvent('view_content', {
      content_type: page.page_type,
      content_id: page.page_id,
      content_name: page.page_name,
      page_url: page.page_url,
    });
  }

  // Pricing view: one event when calculator/pricing enters the viewport.
  const pricingTarget = document.querySelector('.hp-calculator-section, .hp-calculator-panel, [data-tracking-pricing]');
  if (pricingTarget) {
    let fired = false;
    const firePricing = () => {
      if (fired) return;
      fired = true;
      window.trackEvent('view_pricing', { page_type: page.page_type, page_url: page.page_url });
    };
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          firePricing();
          observer.disconnect();
        }
      }, { threshold: 0.25 });
      observer.observe(pricingTarget);
    } else {
      firePricing();
    }
  }

  // Engagement events: use delegated handlers (widgets/sections can be inserted later).
  // Link destinations, names, phone numbers and typed form values are never sent.
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = String(link.getAttribute('href') || '').trim();
    const channelId = link.dataset.contactId || undefined;
    const scope = link.dataset.contactScope || undefined;
    let isWhatsApp = /^whatsapp:\/\//i.test(href);
    try {
      const target = new URL(href, window.location.href);
      isWhatsApp = isWhatsApp || (target.protocol === 'https:' &&
        ['wa.me', 'api.whatsapp.com', 'web.whatsapp.com'].includes(target.hostname.toLowerCase()));
    } catch (_) { /* Invalid URLs are not tracked. */ }

    if (isWhatsApp) {
      window.trackEvent('click_whatsapp', { channel_type: 'whatsapp', contact_id: channelId, scope });
      window.trackEvent('contact', { channel_type: 'whatsapp', scope });
      return;
    }
    if (/^tel:/i.test(href)) {
      window.trackEvent('click_call', { channel_type: 'phone', contact_id: channelId, scope });
      window.trackEvent('contact', { channel_type: 'phone', scope });
      return;
    }
    if (/^mailto:/i.test(href)) {
      window.trackEvent('click_email', { channel_type: 'email', scope });
      window.trackEvent('contact', { channel_type: 'email', scope });
      return;
    }
    if (href.includes('#quote-form') || link.matches('[data-tracking-cta]')) {
      window.trackEvent('click_cta', { action_type: href.includes('#quote-form') ? 'quote_form' : 'cta', scope });
      if (href.includes('#quote-form')) window.trackEvent('contact', { channel_type: 'quote_form' });
    }
  }, { passive: true });

  // Form engagement is NOT a successful conversion. Lead / SubmitQuote fire only after
  // the server has saved an enquiry and rendered its one-time success flash message.
  document.addEventListener('focusin', (event) => {
    const form = event.target.closest('form.hp-quote-form');
    if (!form || form.dataset.trackingStarted === '1') return;
    form.dataset.trackingStarted = '1';
    window.trackEvent('quote_form_start', { form_type: 'quote' });
  });
  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form.hp-quote-form');
    if (!form || form.dataset.trackingAttempted === '1') return;
    form.dataset.trackingAttempted = '1';
    window.trackEvent('quote_form_attempt', { form_type: 'quote' });
  });

  // Track real play/ended events for HTML5 videos; no video URLs or personal data.
  document.addEventListener('play', (event) => {
    const video = event.target;
    if (video.tagName !== 'VIDEO') return;
    if (video.dataset.trackingPlayed === '1') return;
    video.dataset.trackingPlayed = '1';
    window.trackEvent('video_play', { media_type: 'video' });
  }, true);
  document.addEventListener('ended', (event) => {
    if (event.target.tagName !== 'VIDEO') return;
    window.trackEvent('video_complete', { media_type: 'video' });
  }, true);
  // An autoplay video may already be playing before deferred tracking.js executes.
  document.querySelectorAll('video').forEach((video) => {
    if (video.paused || video.ended || video.dataset.trackingPlayed === '1') return;
    video.dataset.trackingPlayed = '1';
    window.trackEvent('video_play', { media_type: 'video' });
  });

  // Successful quote redirect: emit SubmitQuote + Lead only after persistence succeeded.
  const leadSuccess = document.querySelector('[data-tracking-lead-success]');
  if (leadSuccess) {
    const payload = {
      service_id: leadSuccess.dataset.serviceId || undefined,
      source: 'website_quote_form',
    };
    window.trackEvent('submit_quote', payload);
    window.trackEvent('lead', payload);
  }

  // Scroll depth: visible in DataLayer Checker. We keep the app event and also emit the
  // familiar gtm.scrollDepth-shaped event used by many GTM diagnostics.
  const firedDepths = new Set();
  const checkScrollDepth = () => {
    const doc = document.documentElement;
    const scrollable = Math.max(1, doc.scrollHeight - window.innerHeight);
    const percent = Math.min(100, Math.max(0, Math.round((window.scrollY / scrollable) * 100)));
    [25, 50, 75, 90, 100].forEach((threshold) => {
      if (percent < threshold || firedDepths.has(threshold)) return;
      firedDepths.add(threshold);
      const details = { percent: threshold, direction: 'vertical', units: 'percent' };
      window.trackEvent('scroll_depth', details);
      dataLayer.push({
        event: 'gtm.scrollDepth',
        'gtm.scrollThreshold': threshold,
        'gtm.scrollUnits': 'percent',
        'gtm.scrollDirection': 'vertical',
        event_source: 'website_runtime'
      });
    });
  };
  window.addEventListener('scroll', checkScrollDepth, { passive: true });
  window.addEventListener('resize', checkScrollDepth, { passive: true });
  checkScrollDepth();
})();
