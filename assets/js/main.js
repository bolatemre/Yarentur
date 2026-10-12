(function () {
  // Banner geçişi
  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var slides = slider.querySelectorAll('.hero__slide');
    var dots = slider.querySelectorAll('.hero__dots button');
    var cur = 0, timer = null;
    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function go(i) {
      slides[cur].classList.remove('is-on'); if (dots[cur]) dots[cur].classList.remove('is-on');
      cur = (i + slides.length) % slides.length;
      slides[cur].classList.add('is-on'); if (dots[cur]) dots[cur].classList.add('is-on');
    }
    function play() { if (!still && slides.length > 1) timer = setInterval(function () { go(cur + 1); }, 6000); }
    dots.forEach(function (d, i) { d.addEventListener('click', function () { clearInterval(timer); go(i); play(); }); });
    play();
  }
})();

(function () {
  var btn = document.querySelector('.navtoggle');
  var nav = document.getElementById('nav');
  if (!btn || !nav) return;
  function set(open) {
    document.body.classList.toggle('nav-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
  }
  btn.addEventListener('click', function () { set(!document.body.classList.contains('nav-open')); });
  nav.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
})();
