(() => {
  'use strict';
  document.querySelectorAll('[data-delete-user]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm('Delete this fictional user and their related demo records? This cannot be undone.')) {
        event.preventDefault();
      }
    });
  });
})();
