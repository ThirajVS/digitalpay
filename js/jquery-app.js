(function ($) {
  'use strict';
  if (!$) {
    const message = document.querySelector('[data-jquery-status]');
    if (message) message.innerHTML = '<div class="alert alert-error">jQuery could not be loaded. Check the connection and reload this page.</div>';
    return;
  }

  const $list = $('#transaction-list');
  const $details = $('#selected-transaction');
  const $count = $('#result-count');
  let records = [];

  function normalizeType(type) {
    const value = String(type).toLowerCase();
    if (value.includes('transfer')) return 'transfer';
    if (value.includes('bill')) return 'bill';
    if (value.includes('recharge')) return 'recharge';
    if (value.includes('shopping')) return 'shopping';
    if (value.includes('refund')) return 'refund';
    return value.replace(/\s+/g, '-');
  }

  function statusClass(status) {
    return `status-${String(status).toLowerCase().replace(/\s+/g, '-')}`;
  }

  function transactionCard(record, index) {
    const type = normalizeType(record.type);
    const direction = type === 'refund' ? 'credit' : 'debit';
    const amountPrefix = direction === 'credit' ? '+' : '−';
    const icon = { transfer: '↗', bill: '▤', recharge: '⌁', shopping: '▣', refund: '↙' }[type] || '₹';
    return `<article class="transaction-item" id="transaction${index + 101}" tabindex="0"
      data-id="${window.DigitalPay.escapeHtml(record.id)}" data-type="${type}"
      data-status="${String(record.status).toLowerCase()}" data-amount="${Number(record.amount)}">
      <span class="transaction-icon" aria-hidden="true">${icon}</span>
      <span class="transaction-main">
        <span class="transaction-title">${window.DigitalPay.escapeHtml(record.recipient)}</span>
        <span class="transaction-reference">${window.DigitalPay.escapeHtml(record.id)} · ${window.DigitalPay.escapeHtml(record.date)}</span>
      </span>
      <span class="transaction-amount ${direction}">${amountPrefix}${window.DigitalPay.formatCurrency(record.amount)}</span>
      <span class="status ${statusClass(record.status)}">${window.DigitalPay.escapeHtml(record.status)}</span>
    </article>`;
  }

  function render(data) {
    records = data;
    $list.html(data.map(transactionCard).join(''));
    applyFilters();
    $('#remove-selected').prop('disabled', true);
  }

  function showDetails($item) {
    $('.transaction-item').removeClass('selected').attr('aria-selected', 'false');
    $item.addClass('selected').attr('aria-selected', 'true');
    const record = records.find((item) => item.id === $item.data('id'));
    if (!record) return;
    $details.html(`<h3>Selected Transaction</h3>
      <div class="detail-list">
        <div class="detail-row"><span>Reference</span><strong>${window.DigitalPay.escapeHtml(record.id)}</strong></div>
        <div class="detail-row"><span>Type</span><strong>${window.DigitalPay.escapeHtml(record.type)}</strong></div>
        <div class="detail-row"><span>Recipient</span><strong>${window.DigitalPay.escapeHtml(record.recipient)}</strong></div>
        <div class="detail-row"><span>Amount</span><strong>${window.DigitalPay.formatCurrency(record.amount)}</strong></div>
        <div class="detail-row"><span>Status</span><span class="status ${statusClass(record.status)}">${window.DigitalPay.escapeHtml(record.status)}</span></div>
        <div class="detail-row"><span>Date</span><strong>${window.DigitalPay.escapeHtml(record.date)}</strong></div>
      </div>`);
    $('#remove-selected').prop('disabled', false);
  }

  $list.on('click', '.transaction-item', function () {
    showDetails($(this));
  });
  $list.on('keydown', '.transaction-item', function (event) {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      showDetails($(this));
    }
  });

  $('.category-pill').on('click', function () {
    $('.category-pill').removeClass('active').attr('aria-pressed', 'false');
    $(this).addClass('active').attr('aria-pressed', 'true');
    const category = $(this).data('category');
    $('.transaction-item').removeClass('category-match');
    const $matching = category === 'all'
      ? $('.transaction-item')
      : $(`.transaction-item[data-type="${category}"]`);
    $matching.addClass('category-match');
    $('#category-count').text(`${$matching.length} transaction${$matching.length === 1 ? '' : 's'} match this category`);
    $('#filter-type').val(category);
    applyFilters();
  });

  function applyFilters() {
    const term = $('#transaction-search').val().trim().toLowerCase();
    const type = $('#filter-type').val();
    const status = $('#filter-status').val();
    const range = $('#filter-amount').val();
    let visible = 0;
    $('.transaction-item').each(function () {
      const $item = $(this);
      const amount = Number($item.data('amount'));
      const amountMatch = range === 'all' ||
        (range === 'low' && amount < 1000) ||
        (range === 'medium' && amount >= 1000 && amount <= 5000) ||
        (range === 'high' && amount > 5000);
      const match = (!term || $item.text().toLowerCase().includes(term)) &&
        (type === 'all' || $item.data('type') === type) &&
        (status === 'all' || $item.data('status') === status) && amountMatch;
      $item.toggle(match);
      if (match) visible += 1;
    });
    $count.text(`Showing ${visible} of ${$('.transaction-item').length} transactions`);
  }

  $('#transaction-search').on('input', applyFilters).on('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      applyFilters();
      window.DigitalPay.toast('Transaction search applied.');
    }
  });
  $('#filter-type, #filter-status, #filter-amount').on('change', applyFilters);

  $('#add-transaction').on('click', function () {
    const id = `DP${Math.floor(100000 + Math.random() * 899999)}`;
    records.unshift({ id, type: 'Money Transfer', recipient: 'New Demo Payment', amount: 750, status: 'Pending', date: new Date().toISOString().slice(0, 10) });
    render(records);
    window.DigitalPay.toast('A fictional transaction was added.', 'success');
  });
  $('#remove-selected').on('click', function () {
    const id = $('.transaction-item.selected').data('id');
    if (!id) return;
    records = records.filter((record) => record.id !== id);
    render(records);
    $details.html('<div class="empty-state"><p>Select a transaction to inspect its details.</p></div>');
    window.DigitalPay.toast('Selected transaction removed from this demo view.');
  });

  function loadTransactions() {
    const $button = $('#load-transactions');
    const $status = $('[data-jquery-status]');
    $button.prop('disabled', true);
    $status.html('<div class="loading"><span class="spinner"></span><span>Loading transactions…</span></div>');
    $.ajax({ url: 'data/transactions.json', dataType: 'json', cache: false, timeout: 8000 })
      .done(function (data) {
        if (!Array.isArray(data)) throw new Error('Invalid JSON structure');
        render(data);
        $status.html('<div class="alert alert-success">✓ Latest fictional transactions loaded successfully.</div>');
        window.DigitalPay.toast('Latest transactions loaded.', 'success');
      })
      .fail(function () {
        $status.html(`<div class="alert alert-error"><span>Unable to load transaction data. Please check your connection and try again.</span><button type="button" class="btn btn-small btn-danger" id="retry-transactions">Retry</button></div>`);
      })
      .always(function () { $button.prop('disabled', false); });
  }

  $('#load-transactions').on('click', loadTransactions);
  $(document).on('click', '#retry-transactions', loadTransactions);

  render([
    { id: 'DP100001', type: 'Money Transfer', recipient: 'Aarav Demo', amount: 2500, status: 'Successful', date: '2026-09-21' },
    { id: 'DP100002', type: 'Bill Payment', recipient: 'Northstar Electricity', amount: 1840, status: 'Pending', date: '2026-09-20' },
    { id: 'DP100003', type: 'Mobile Recharge', recipient: 'Sample Mobile', amount: 499, status: 'Successful', date: '2026-09-19' },
    { id: 'DP100004', type: 'Shopping', recipient: 'Demo Market', amount: 3299, status: 'Failed', date: '2026-09-18' },
    { id: 'DP100005', type: 'Refund', recipient: 'Demo Market Refund', amount: 799, status: 'Refunded', date: '2026-09-17' }
  ]);
}(window.jQuery));
