(() => {
  'use strict';
  const paymentForm = document.querySelector('#payment-form');
  const result = document.querySelector('[data-payment-result]');
  const preview = document.querySelector('[data-service-preview]');
  const beneficiaryList = document.querySelector('[data-beneficiary-list]');
  const beneficiaryForm = document.querySelector('#beneficiary-form');
  const search = document.querySelector('[data-beneficiary-search]');
  const category = document.querySelector('[data-beneficiary-category]');

  const beneficiaries = [
    { name: 'Aarav Demo', account: 'DP-USER-2041', category: 'personal', favorite: true },
    { name: 'Northstar Internet', account: 'DP-BILL-6812', category: 'biller', favorite: false },
    { name: 'Mira Fictional', account: 'DP-USER-4930', category: 'personal', favorite: false }
  ];

  const renderBeneficiaries = () => {
    if (!beneficiaryList) return;
    const term = search?.value.trim().toLowerCase() || '';
    const selectedCategory = category?.value || 'all';
    const filtered = beneficiaries.filter((item) =>
      (!term || item.name.toLowerCase().includes(term) || item.account.toLowerCase().includes(term)) &&
      (selectedCategory === 'all' || item.category === selectedCategory)
    );
    beneficiaryList.innerHTML = filtered.map((item) => {
      const originalIndex = beneficiaries.indexOf(item);
      return `<li class="beneficiary-item" data-beneficiary-index="${originalIndex}">
        <span class="avatar" aria-hidden="true">${item.name.split(' ').map((word) => word[0]).join('').slice(0, 2)}</span>
        <span><strong data-beneficiary-name>${window.DigitalPay.escapeHtml(item.name)}</strong><small class="muted" style="display:block">${window.DigitalPay.escapeHtml(item.account)}</small></span>
        <span class="beneficiary-actions">
          <button type="button" class="icon-btn tooltip" data-favorite data-tooltip="Add to favorites" aria-label="Toggle favorite">${item.favorite ? '★' : '☆'}</button>
          <button type="button" class="icon-btn tooltip" data-send data-tooltip="Send money" aria-label="Send money to ${window.DigitalPay.escapeHtml(item.name)}">→</button>
        </span>
      </li>`;
    }).join('') || '<li class="empty-state">No beneficiaries match this search.</li>';
  };

  search?.addEventListener('input', renderBeneficiaries);
  search?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      renderBeneficiaries();
      window.DigitalPay.toast(`Search applied for “${search.value.trim()}”.`);
    }
  });
  category?.addEventListener('change', renderBeneficiaries);

  beneficiaryList?.addEventListener('click', (event) => {
    const item = event.target.closest('[data-beneficiary-index]');
    if (!item) return;
    const beneficiary = beneficiaries[Number(item.dataset.beneficiaryIndex)];
    if (event.target.closest('[data-favorite]')) {
      beneficiary.favorite = !beneficiary.favorite;
      renderBeneficiaries();
      window.DigitalPay.toast(`${beneficiary.name} ${beneficiary.favorite ? 'added to' : 'removed from'} favorites.`, 'success');
    }
    if (event.target.closest('[data-send]')) {
      const recipient = paymentForm?.querySelector('[name="recipient"]');
      if (recipient) recipient.value = beneficiary.name;
      paymentForm?.scrollIntoView({ behavior: 'smooth' });
      recipient?.focus();
    }
  });

  beneficiaryList?.addEventListener('dblclick', (event) => {
    const name = event.target.closest('[data-beneficiary-name]');
    if (!name) return;
    name.contentEditable = 'true';
    name.focus();
    document.getSelection()?.selectAllChildren(name);
    window.DigitalPay.toast('Edit the name, then press Enter.');
  });
  beneficiaryList?.addEventListener('keydown', (event) => {
    const name = event.target.closest('[data-beneficiary-name][contenteditable="true"]');
    if (!name || event.key !== 'Enter') return;
    event.preventDefault();
    const item = name.closest('[data-beneficiary-index]');
    beneficiaries[Number(item.dataset.beneficiaryIndex)].name = name.textContent.trim() || 'Unnamed beneficiary';
    name.contentEditable = 'false';
    renderBeneficiaries();
    window.DigitalPay.toast('Beneficiary updated.', 'success');
  });

  beneficiaryForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!beneficiaryForm.checkValidity()) {
      beneficiaryForm.reportValidity();
      return;
    }
    const data = new FormData(beneficiaryForm);
    beneficiaries.push({ name: data.get('beneficiary_name'), account: data.get('beneficiary_account'), category: data.get('beneficiary_category'), favorite: false });
    beneficiaryForm.reset();
    renderBeneficiaries();
    window.DigitalPay.toast('Beneficiary added to this demo session.', 'success');
  });

  document.querySelectorAll('[data-service-card]').forEach((card) => {
    const showPreview = () => {
      if (!preview) return;
      preview.innerHTML = `<strong>${window.DigitalPay.escapeHtml(card.dataset.title)}</strong><p>${window.DigitalPay.escapeHtml(card.dataset.preview)}</p>`;
    };
    card.addEventListener('mouseover', showPreview);
    card.addEventListener('focusin', showPreview);
    card.addEventListener('mouseout', () => {
      if (preview) preview.innerHTML = '<strong>Explore a service</strong><p>Hover or focus a service to see how the simulated payment flow works.</p>';
    });
  });

  paymentForm?.querySelectorAll('input, select').forEach((field) => {
    field.addEventListener('blur', () => {
      field.setAttribute('aria-invalid', String(!field.checkValidity()));
    });
  });

  paymentForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!paymentForm.checkValidity()) {
      paymentForm.reportValidity();
      window.DigitalPay.toast('Complete all required payment details.', 'error');
      return;
    }
    const data = new FormData(paymentForm);
    const amount = Number(data.get('amount'));
    if (!Number.isFinite(amount) || amount <= 0 || amount > 100000) {
      paymentForm.elements.amount.setAttribute('aria-invalid', 'true');
      window.DigitalPay.toast('Enter an amount between ₹1 and ₹1,00,000.', 'error');
      return;
    }
    const transactionId = `DP${Date.now().toString().slice(-8)}`;
    const values = {
      transactionId,
      amount: amount.toLocaleString('en-IN', { style: 'currency', currency: 'INR' }),
      recipient: data.get('recipient'),
      type: data.get('payment_type'),
      date: new Intl.DateTimeFormat('en-IN', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date())
    };
    result.hidden = false;
    result.innerHTML = `<div class="alert alert-success"><strong>✓</strong><span>Payment completed successfully in the demo environment.</span></div>
      <h3 style="margin-top:1rem">Payment Successful</h3>
      <div class="detail-list">
        <div class="detail-row"><span>Transaction ID</span><strong>${values.transactionId}</strong></div>
        <div class="detail-row"><span>Amount</span><strong>${values.amount}</strong></div>
        <div class="detail-row"><span>Recipient</span><strong>${window.DigitalPay.escapeHtml(values.recipient)}</strong></div>
        <div class="detail-row"><span>Payment type</span><strong>${window.DigitalPay.escapeHtml(values.type)}</strong></div>
        <div class="detail-row"><span>Date</span><strong>${values.date}</strong></div>
        <div class="detail-row"><span>Status</span><span class="status status-successful">Successful</span></div>
      </div>`;
    result.scrollIntoView({ behavior: 'smooth', block: 'center' });
    window.DigitalPay.toast('Payment simulation completed successfully.', 'success');
    paymentForm.reset();
  });

  renderBeneficiaries();
})();
