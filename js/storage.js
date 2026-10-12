(() => {
  'use strict';
  const services = document.querySelectorAll('[data-payment-service]');
  const queue = document.querySelector('[data-payment-queue]');
  const empty = document.querySelector('[data-queue-empty]');
  const count = document.querySelector('[data-queue-count]');
  const sessionNote = document.querySelector('[data-session-note]');
  let queueItems = [];

  if (!queue) return;

  const renderQueue = () => {
    queue.querySelectorAll('.queue-item').forEach((item) => item.remove());
    empty.hidden = queueItems.length > 0;
    count.textContent = String(queueItems.length);
    queueItems.forEach((service) => {
      const item = document.createElement('li');
      item.className = 'queue-item';
      item.dataset.id = service.id;
      item.innerHTML = `<span class="card-icon" aria-hidden="true">${service.icon}</span><span><strong>${window.DigitalPay.escapeHtml(service.name)}</strong><small class="muted" style="display:block">Ready to configure</small></span><button class="icon-btn tooltip" type="button" data-remove-queue data-tooltip="Remove ${window.DigitalPay.escapeHtml(service.name)}" aria-label="Remove ${window.DigitalPay.escapeHtml(service.name)}">×</button>`;
      queue.append(item);
    });
  };

  const addService = (service) => {
    if (queueItems.some((item) => item.id === service.id)) {
      window.DigitalPay.toast(`${service.name} is already in the queue.`);
      return;
    }
    queueItems.push(service);
    sessionStorage.setItem('digitalpay-session-queue', JSON.stringify(queueItems));
    renderQueue();
    window.DigitalPay.toast(`${service.name} added to the payment queue.`, 'success');
  };

  services.forEach((service) => {
    service.addEventListener('dragstart', (event) => {
      service.classList.add('dragging');
      event.dataTransfer.effectAllowed = 'copy';
      event.dataTransfer.setData('text/plain', JSON.stringify({
        id: service.dataset.serviceId,
        name: service.dataset.serviceName,
        icon: service.dataset.serviceIcon
      }));
    });
    service.addEventListener('dragend', () => service.classList.remove('dragging'));
    service.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        addService({ id: service.dataset.serviceId, name: service.dataset.serviceName, icon: service.dataset.serviceIcon });
      }
    });
  });

  queue.addEventListener('dragover', (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'copy';
    queue.classList.add('drag-over');
  });
  queue.addEventListener('dragleave', (event) => {
    if (!queue.contains(event.relatedTarget)) queue.classList.remove('drag-over');
  });
  queue.addEventListener('drop', (event) => {
    event.preventDefault();
    queue.classList.remove('drag-over');
    try { addService(JSON.parse(event.dataTransfer.getData('text/plain'))); }
    catch { window.DigitalPay.toast('That item could not be added.', 'error'); }
  });
  queue.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-remove-queue]');
    if (!removeButton) return;
    const item = removeButton.closest('.queue-item');
    queueItems = queueItems.filter((service) => service.id !== item.dataset.id);
    sessionStorage.setItem('digitalpay-session-queue', JSON.stringify(queueItems));
    renderQueue();
    window.DigitalPay.toast('Payment item removed.');
  });

  document.querySelector('[data-save-queue]')?.addEventListener('click', () => {
    localStorage.setItem('digitalpay-payment-queue', JSON.stringify(queueItems));
    window.DigitalPay.toast(`Saved ${queueItems.length} payment item${queueItems.length === 1 ? '' : 's'} locally.`, 'success');
  });
  document.querySelector('[data-retrieve-queue]')?.addEventListener('click', () => {
    try { queueItems = JSON.parse(localStorage.getItem('digitalpay-payment-queue')) || []; }
    catch { queueItems = []; }
    sessionStorage.setItem('digitalpay-session-queue', JSON.stringify(queueItems));
    renderQueue();
    window.DigitalPay.toast(queueItems.length ? 'Saved queue restored.' : 'No saved queue was found.');
  });
  document.querySelector('[data-clear-queue]')?.addEventListener('click', () => {
    if (queueItems.length && !window.confirm('Clear all payment items from local and session storage?')) return;
    queueItems = [];
    localStorage.removeItem('digitalpay-payment-queue');
    sessionStorage.removeItem('digitalpay-session-queue');
    renderQueue();
    window.DigitalPay.toast('Payment queue cleared.');
  });

  sessionNote?.addEventListener('input', () => sessionStorage.setItem('digitalpay-session-note', sessionNote.value));
  sessionNote.value = sessionStorage.getItem('digitalpay-session-note') || '';
  try { queueItems = JSON.parse(sessionStorage.getItem('digitalpay-session-queue')) || []; }
  catch { queueItems = []; }
  renderQueue();
})();
