(() => {
  'use strict';

  const root = document.documentElement;
  const body = document.body;

  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  window.DigitalPay = window.DigitalPay || {};
  window.DigitalPay.escapeHtml = escapeHtml;

  window.DigitalPay.toast = (message, type = 'info') => {
    let region = document.querySelector('.toast-region');
    if (!region) {
      region = document.createElement('div');
      region.className = 'toast-region';
      region.setAttribute('aria-live', 'polite');
      document.body.append(region);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    const icon = type === 'success' ? '✓' : type === 'error' ? '!' : 'i';
    toast.innerHTML = `<strong aria-hidden="true">${icon}</strong><span>${escapeHtml(message)}</span>`;
    region.append(toast);
    window.setTimeout(() => toast.remove(), 4200);
  };

  const savedTheme = localStorage.getItem('digitalpay-theme');
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    root.dataset.theme = 'dark';
  }

  const themeButton = document.querySelector('[data-theme-toggle]');
  const syncThemeButton = () => {
    if (!themeButton) return;
    const dark = root.dataset.theme === 'dark';
    themeButton.setAttribute('aria-label', dark ? 'Switch to light theme' : 'Switch to dark theme');
    themeButton.textContent = dark ? '☀' : '☾';
  };
  syncThemeButton();
  themeButton?.addEventListener('click', () => {
    const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = nextTheme;
    localStorage.setItem('digitalpay-theme', nextTheme);
    syncThemeButton();
    window.DigitalPay.toast(`${nextTheme === 'dark' ? 'Dark' : 'Light'} theme enabled`);
  });

  const navToggle = document.querySelector('[data-nav-toggle]');
  const siteNav = document.querySelector('[data-site-nav]');
  const setNav = (open) => {
    if (!siteNav || !navToggle) return;
    siteNav.classList.toggle('open', open);
    body.classList.toggle('nav-open', open);
    navToggle.setAttribute('aria-expanded', String(open));
    navToggle.textContent = open ? '✕' : '☰';
  };
  navToggle?.addEventListener('click', () => setNav(!siteNav.classList.contains('open')));
  siteNav?.addEventListener('click', (event) => {
    if (event.target.closest('a')) setNav(false);
  });

  const more = document.querySelector('.nav-more');
  const moreButton = more?.querySelector('.nav-more-toggle');
  moreButton?.addEventListener('click', () => {
    const isOpen = more.classList.toggle('open');
    moreButton.setAttribute('aria-expanded', String(isOpen));
  });
  document.addEventListener('click', (event) => {
    if (more && !more.contains(event.target)) {
      more.classList.remove('open');
      moreButton?.setAttribute('aria-expanded', 'false');
    }
  });

  const page = body.dataset.page;
  document.querySelectorAll('[data-nav-page]').forEach((link) => {
    const active = link.dataset.navPage === page;
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', 'page');
  });

  const updateClock = () => {
    const now = new Date();
    document.querySelectorAll('[data-current-datetime]').forEach((element) => {
      element.textContent = new Intl.DateTimeFormat('en-IN', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      }).format(now);
    });
    document.querySelectorAll('[data-current-year]').forEach((element) => {
      element.textContent = now.getFullYear();
    });
  };
  updateClock();
  window.setInterval(updateClock, 1000);

  const notificationButton = document.querySelector('[data-notification-toggle]');
  const notificationPanel = document.querySelector('[data-notification-panel]');
  notificationButton?.addEventListener('click', () => {
    const open = notificationPanel.classList.toggle('open');
    notificationButton.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', (event) => {
    if (notificationPanel && notificationButton &&
      !notificationPanel.contains(event.target) && !notificationButton.contains(event.target)) {
      notificationPanel.classList.remove('open');
      notificationButton.setAttribute('aria-expanded', 'false');
    }
  });

  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length) {
    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const element = entry.target;
        const target = Number(element.dataset.counter || 0);
        const prefix = element.dataset.prefix || '';
        const suffix = element.dataset.suffix || '';
        const start = performance.now();
        const duration = 900;
        const frame = (time) => {
          const progress = Math.min((time - start) / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          const value = Math.round(target * eased);
          element.textContent = `${prefix}${value.toLocaleString('en-IN')}${suffix}`;
          if (progress < 1) requestAnimationFrame(frame);
        };
        requestAnimationFrame(frame);
        observer.unobserve(element);
      });
    }, { threshold: .35 });
    counters.forEach((counter) => counterObserver.observe(counter));
  }

  const revealElements = document.querySelectorAll('.reveal');
  if (revealElements.length) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: .12 });
    revealElements.forEach((element) => revealObserver.observe(element));
  }

  const slides = [...document.querySelectorAll('.slide')];
  const dots = [...document.querySelectorAll('.slider-dot')];
  let activeSlide = 0;
  const showSlide = (index) => {
    activeSlide = index % slides.length;
    slides.forEach((slide, slideIndex) => slide.classList.toggle('active', slideIndex === activeSlide));
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle('active', dotIndex === activeSlide);
      dot.setAttribute('aria-current', dotIndex === activeSlide ? 'true' : 'false');
    });
  };
  dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));
  if (slides.length > 1) window.setInterval(() => showSlide(activeSlide + 1), 5200);

  const scrollButton = document.querySelector('[data-scroll-top]');
  const syncScrollButton = () => scrollButton?.classList.toggle('visible', window.scrollY > 500);
  window.addEventListener('scroll', syncScrollButton, { passive: true });
  syncScrollButton();
  scrollButton?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  document.querySelectorAll('[data-validated-form]').forEach((form) => {
    const fields = form.querySelectorAll('input, select, textarea');
    const validateField = (field) => {
      const error = field.closest('.field')?.querySelector('.field-error');
      let message = '';
      if (!field.checkValidity()) {
        if (field.validity.valueMissing) message = 'This field is required.';
        else if (field.validity.typeMismatch) message = `Enter a valid ${field.type}.`;
        else if (field.validity.patternMismatch) message = field.dataset.patternMessage || 'Use the requested format.';
        else if (field.validity.rangeUnderflow) message = `Minimum value is ${field.min}.`;
        else if (field.validity.rangeOverflow) message = `Maximum value is ${field.max}.`;
        else message = 'Please check this value.';
      }
      field.setAttribute('aria-invalid', String(Boolean(message)));
      if (error) error.textContent = message;
      return !message;
    };
    fields.forEach((field) => field.addEventListener('blur', () => validateField(field)));
    form.addEventListener('reset', () => {
      window.setTimeout(() => {
        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        form.querySelectorAll('.field-error').forEach((error) => { error.textContent = ''; });
        window.DigitalPay.toast('Form cleared');
      });
    });
    form.addEventListener('submit', (event) => {
      const valid = [...fields].every(validateField);
      if (!valid) {
        event.preventDefault();
        form.querySelector('[aria-invalid="true"]')?.focus();
        window.DigitalPay.toast('Please correct the highlighted fields.', 'error');
      } else if (form.hasAttribute('data-demo-form')) {
        event.preventDefault();
        window.DigitalPay.toast('Demo request received. No message was sent externally.', 'success');
      }
    });
  });

  document.querySelector('[data-print-report]')?.addEventListener('click', () => window.print());

  const profileInput = document.querySelector('[data-profile-input]');
  const profilePreview = document.querySelector('[data-profile-preview]');
  profileInput?.addEventListener('change', () => {
    const file = profileInput.files?.[0];
    if (!file || !profilePreview) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
      profileInput.value = '';
      window.DigitalPay.toast('Choose a JPG, PNG or WebP image under 2 MB.', 'error');
      return;
    }
    profilePreview.src = URL.createObjectURL(file);
  });
})();
