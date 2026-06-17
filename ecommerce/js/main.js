/* ShopSphere — small progressive-enhancement layer.
   Everything works without JS (forms POST normally); JS just improves UX. */
(function () {
  'use strict';

  // --- Mobile navigation toggle -------------------------------------------
  var toggle = document.getElementById('navToggle');
  var links = document.getElementById('navLinks');
  if (toggle && links) {
    toggle.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // --- Confirm destructive actions ----------------------------------------
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // --- AJAX "add to cart" (falls back to a normal form POST) --------------
  document.querySelectorAll('form.js-add-to-cart').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('[type=submit]');
      if (btn) { btn.disabled = true; }

      fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data && data.success) {
            updateCartBadge(data.cart_count);
            flash(data.message || 'Added to cart', 'success');
          } else {
            flash((data && data.message) || 'Could not add to cart', 'error');
          }
        })
        .catch(function () {
          // Network/JSON failure — fall back to a full submit.
          form.submit();
        })
        .finally(function () {
          if (btn) { btn.disabled = false; }
        });
    });
  });

  function updateCartBadge(count) {
    if (typeof count !== 'number') { return; }
    var cartLink = document.querySelector('.nav-cart');
    if (!cartLink) { return; }
    var badge = cartLink.querySelector('.cart-badge');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cart-badge';
      cartLink.appendChild(badge);
    }
    badge.textContent = count;
  }

  // Lightweight transient toast for AJAX feedback.
  function flash(message, type) {
    var el = document.createElement('div');
    el.className = 'flash flash-' + (type || 'info');
    el.textContent = message;
    el.style.position = 'fixed';
    el.style.right = '20px';
    el.style.bottom = '20px';
    el.style.zIndex = '999';
    el.style.boxShadow = '0 8px 24px rgba(16,24,40,.18)';
    document.body.appendChild(el);
    setTimeout(function () {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 2200);
  }
})();
