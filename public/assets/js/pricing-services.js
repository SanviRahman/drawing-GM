(() => {
  'use strict';
  // Existing quote form remains unchanged. Selecting a service card pre-fills its service.
  document.querySelectorAll('[data-request-service]').forEach((link) => {
    link.addEventListener('click', () => {
      const select = document.querySelector('#quote-form select[name="service_id"]');
      const serviceId = link.getAttribute('data-request-service');
      if (!select || !serviceId) return;
      const option = [...select.options].find((item) => item.value === serviceId);
      if (option) {
        select.value = serviceId;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });
})();
