(function () {
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  // menu + search toggles
  $$('[data-toggle]').forEach(function (b) {
    b.addEventListener('click', function (ev) {
      ev.preventDefault();
      if (b.dataset.toggle === 'nav') { $('#nav').classList.toggle('open'); }
      if (b.dataset.toggle === 'search') { var s = $('#search'); s.hidden = !s.hidden; if (!s.hidden) { $('input', s).focus(); } }
    });
  });
  // grid density toggle (3 / 4 columns) - like the reference shop
  $$('[data-cols]').forEach(function (b) {
    b.addEventListener('click', function () {
      var g = $('.grid'); if (!g) return;
      g.classList.remove('cols-3', 'cols-4'); g.classList.add('cols-' + b.dataset.cols);
      $$('[data-cols]').forEach(function (x) { x.classList.toggle('on', x === b); });
    });
  });
  // size picker: enable add-to-cart only when a size is chosen
  var sizeForm = $('#add-form');
  if (sizeForm) {
    var btn = $('button[type=submit]', sizeForm);
    $$('input[name=size]', sizeForm).forEach(function (r) {
      r.addEventListener('change', function () {
        btn.disabled = false; btn.textContent = 'Add to bag';
        var s = $('#stock-note'); if (s) { s.textContent = r.dataset.stock <= 3 ? 'Only ' + r.dataset.stock + ' left in this size' : ''; }
      });
    });
  }
  // checkout: show card fields only for card payments, format card number
  $$('input[name=pay]').forEach(function (r) {
    r.addEventListener('change', function () {
      var box = $('#card-box'); if (box) { box.hidden = !(r.value === 'card' && r.checked); }
    });
  });
  var cn = $('#card_number');
  if (cn) { cn.addEventListener('input', function () { cn.value = cn.value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim(); }); }
  var ex = $('#card_exp');
  if (ex) { ex.addEventListener('input', function () { var v = ex.value.replace(/\D/g, '').slice(0, 4); ex.value = v.length > 2 ? v.slice(0, 2) + '/' + v.slice(2) : v; }); }
  // saved address / card radios hide the "new" forms
  $$('input[name=addr_choice]').forEach(function (r) {
    r.addEventListener('change', function () { var n = $('#new-addr'); if (n) { n.hidden = r.value !== 'new'; } });
  });
  $$('input[name=card_choice]').forEach(function (r) {
    r.addEventListener('change', function () { var n = $('#new-card'); if (n) { n.hidden = r.value !== 'new'; } });
  });
  // confirm delete
  $$('[data-confirm]').forEach(function (f) { f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); }); });
})();
