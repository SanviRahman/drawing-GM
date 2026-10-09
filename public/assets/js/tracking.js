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
    return { ...safeEventData(eventData), ...mapped };
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
        click_call: 'click_call',
        submit_quote: 'generate_lead',
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

  // Contact / WhatsApp / phone click events. Never send the raw phone number.
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = String(link.getAttribute('href') || '');
    const label = String(link.getAttribute('aria-label') || link.textContent || '').trim().slice(0, 160);

    if (/https?:\/\/(wa\.me|api\.whatsapp\.com)\//i.test(href)) {
      window.trackEvent('click_whatsapp', {
        channel_type: 'whatsapp',
        contact_id: link.dataset.contactId || undefined,
        contact_label: link.dataset.contactLabel || label || undefined,
        scope: link.dataset.contactScope || undefined,
      });
      window.trackEvent('contact', { channel_type: 'whatsapp', contact_label: link.dataset.contactLabel || label || undefined });
      return;
    }

    if (/^tel:/i.test(href)) {
      window.trackEvent('click_call', {
        channel_type: 'phone',
        contact_id: link.dataset.contactId || undefined,
        contact_label: link.dataset.contactLabel || label || undefined,
      });
      window.trackEvent('contact', { channel_type: 'phone', contact_label: link.dataset.contactLabel || label || undefined });
      return;
    }

    if (href.includes('#quote-form')) {
      window.trackEvent('contact', { channel_type: 'quote_form', contact_label: label || 'Quote form' });
    }
  }, { passive: true });

  // Successful quote redirect: emit SubmitQuote + Lead only after persistence succeeded.
  const leadSuccess = document.querySelector('[data-tracking-lead-success]');
  if (leadSuccess) {
    const payload = {
      lead_reference: leadSuccess.dataset.leadReference || undefined,
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
