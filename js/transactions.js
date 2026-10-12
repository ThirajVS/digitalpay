// Shared transaction utilities for pages that do not require jQuery.
(() => {
  'use strict';
  window.DigitalPay = window.DigitalPay || {};
  window.DigitalPay.formatCurrency = (amount) => Number(amount).toLocaleString('en-IN', {
    style: 'currency', currency: 'INR', maximumFractionDigits: 2
  });
})();
