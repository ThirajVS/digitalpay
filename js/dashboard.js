(() => {
  'use strict';
  const refreshButton = document.querySelector('[data-refresh-dashboard]');
  refreshButton?.addEventListener('click', () => {
    refreshButton.disabled = true;
    refreshButton.textContent = 'Refreshing…';
    window.setTimeout(() => {
      document.querySelectorAll('[data-live-value]').forEach((element) => {
        element.classList.remove('animate-zoom');
        void element.offsetWidth;
        element.classList.add('animate-zoom');
      });
      refreshButton.disabled = false;
      refreshButton.textContent = '↻ Refresh';
      window.DigitalPay.toast('Dashboard figures are up to date.', 'success');
    }, 650);
  });

  document.querySelectorAll('[data-quick-action]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.dataset.target;
      if (target) window.location.href = target;
    });
  });
})();
