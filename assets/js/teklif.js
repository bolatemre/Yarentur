// Teklif sihirbazı: JS yoksa form tek sayfa olarak çalışır
(function () {
  var form = document.querySelector('[data-wizard]');
  if (!form) return;
  var steps = Array.prototype.slice.call(form.querySelectorAll('.wiz__step'));
  if (steps.length < 2) return;
  var back = form.querySelector('[data-wiz-back]');
  var next = form.querySelector('[data-wiz-next]');
  var send = form.querySelector('[data-wiz-send]');
  var bar = form.querySelector('[data-wiz-progress]');
  var count = form.querySelector('[data-wiz-count]');
  var cur = 0;
  form.classList.add('wiz--on');
  form.noValidate = true;

  function fields(step) { return step.querySelectorAll('input, select, textarea'); }
  function valid(step) {
    var list = fields(step);
    for (var i = 0; i < list.length; i++) {
      if (!list[i].checkValidity()) { list[i].reportValidity(); list[i].focus(); return false; }
    }
    return true;
  }
  function show(i, focus) {
    cur = Math.max(0, Math.min(i, steps.length - 1));
    steps.forEach(function (s, n) { s.hidden = n !== cur; });
    back.hidden = cur === 0;
    next.hidden = cur === steps.length - 1;
    send.hidden = cur !== steps.length - 1;
    bar.style.width = ((cur + 1) / steps.length * 100) + '%';
    count.textContent = 'Adım ' + (cur + 1) + ' / ' + steps.length;
    if (focus) {
      var f = steps[cur].querySelector('input:not([type=radio]), select, textarea');
      if (f) f.focus({ preventScroll: true });
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
  next.addEventListener('click', function () { if (valid(steps[cur])) show(cur + 1, true); });
  back.addEventListener('click', function () { show(cur - 1, true); });
  form.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && cur < steps.length - 1) {
      e.preventDefault(); next.click();
    }
  });
  form.addEventListener('submit', function (e) {
    for (var i = 0; i < steps.length; i++) {
      var list = fields(steps[i]);
      for (var j = 0; j < list.length; j++) {
        if (!list[j].checkValidity()) {
          e.preventDefault(); show(i, false); list[j].reportValidity(); list[j].focus(); return;
        }
      }
    }
    send.disabled = true;
    send.textContent = 'Gönderiliyor…';
  });
  var start = parseInt(form.getAttribute('data-start') || '1', 10) - 1;
  show(start, false);
})();
