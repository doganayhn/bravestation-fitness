<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BraveStation</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-white">
  <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-sm z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center gap-2">
          <img
  src="assets/images/logo.jpeg?v=1"
  alt="BraveStation Logo"
  class="w-12 h-12 rounded-lg object-cover"
/>

<span class="font-bold text-xl">BraveStation</span>
</div>

<?php if (is_logged_in()): ?>
  <?php $me = user(); ?>
  <a href="<?= is_admin() ? 'admin.php' : 'dashboard.php' ?>"
     class="px-6 py-2 bg-orange-600 text-white rounded-full hover:bg-orange-700 transition-colors flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-white/90"></span>
    <?= e($me['full_name']) ?>
  </a>
<?php else: ?>
  <button id="openLogin"
    class="px-6 py-2 bg-orange-600 text-white rounded-full hover:bg-orange-700 transition-colors">
    Sign In
  </button>
<?php endif; ?>

</div>
</div>
</nav>

<div class="relative">
  <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
  <div class="absolute -top-8 left-1/2 -translate-x-1/2 w-[520px] h-16 bg-orange-500/10 blur-3xl rounded-full"></div>
</div>

<section class="relative pt-24 pb-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 overflow-hidden">

  <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
       style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.35) 1px, transparent 0);
              background-size: 22px 22px;"></div>

  <div class="absolute -top-24 -left-24 w-[420px] h-[420px] bg-orange-500/10 blur-3xl rounded-full"></div>
  <div class="absolute -bottom-24 -right-24 w-[520px] h-[520px] bg-orange-500/10 blur-3xl rounded-full"></div>

  <div class="relative max-w-7xl mx-auto">

    <div class="relative mb-10 reveal">
      <div class="absolute -inset-6 bg-orange-500/20 blur-3xl rounded-full"></div>

      <div class="relative rounded-3xl overflow-hidden ring-1 ring-white/10 shadow-2xl">
        <div id="heroCarousel" class="flex transition-transform duration-700 will-change-transform">
          <div class="min-w-full relative">
            <img
              src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1600&q=80"
              class="w-full h-[360px] md:h-[420px] object-cover"
              alt="Gym training"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 bottom-6 md:left-10 md:bottom-10 text-white max-w-xl">
              <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs md:text-sm mb-3">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Strength • Performance • Discipline
              </p>
              <h3 class="text-2xl md:text-4xl font-extrabold leading-tight">
                Train like an athlete.
              </h3>
              <p class="text-sm md:text-base text-gray-200/90 mt-2">
                Premium free weights, functional zones, and coached programs.
              </p>
            </div>
          </div>

          <div class="min-w-full relative">
            <img
              src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1600&q=80"
              class="w-full h-[360px] md:h-[420px] object-cover"
              alt="Dumbbells gym"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 bottom-6 md:left-10 md:bottom-10 text-white max-w-xl">
              <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs md:text-sm mb-3">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Modern Equipment • Clean Space
              </p>
              <h3 class="text-2xl md:text-4xl font-extrabold leading-tight">
                Everything you need to progress.
              </h3>
              <p class="text-sm md:text-base text-gray-200/90 mt-2">
                From machines to free weights — built for real results.
              </p>
            </div>
          </div>

          <div class="min-w-full relative">
            <img
              src="https://st3.depositphotos.com/13194036/19437/i/450/depositphotos_194379936-stock-photo-male-personal-trainer-helping-sportswoman.jpg"
              class="w-full h-[360px] md:h-[420px] object-cover"
              alt="Personal training"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 bottom-6 md:left-10 md:bottom-10 text-white max-w-xl">
              <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs md:text-sm mb-3">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Coaching • Classes • Accountability
              </p>
              <h3 class="text-2xl md:text-4xl font-extrabold leading-tight">
                Train with expert coaches.
              </h3>
              <p class="text-sm md:text-base text-gray-200/90 mt-2">
                Personalized guidance to keep you consistent and injury-free.
              </p>
            </div>
          </div>
        </div>

        <button id="carPrev"
          class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white grid place-items-center backdrop-blur transition">
          ‹
        </button>
        <button id="carNext"
          class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white grid place-items-center backdrop-blur transition">
          ›
        </button>

        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
          <button class="carDot w-2.5 h-2.5 rounded-full bg-white/70"></button>
          <button class="carDot w-2.5 h-2.5 rounded-full bg-white/25"></button>
          <button class="carDot w-2.5 h-2.5 rounded-full bg-white/25"></button>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div class="text-white reveal">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 text-sm mb-6">
          <span class="w-2 h-2 rounded-full bg-orange-500"></span>
          BraveStation • Premium Gym Experience
        </div>

        <h1 class="text-5xl lg:text-6xl font-extrabold leading-tight">
          Stronger body.
          <span class="text-orange-500">Sharper mind.</span>
          <br class="hidden lg:block" />
          Better life.
        </h1>

        <p class="text-lg lg:text-xl text-gray-200/90 mt-6 max-w-xl">
          Join BraveStation and access elite trainers, modern equipment, and programs that push you forward.
          Track your memberships and purchases inside your account.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
          <button id="openLogin2"
            class="px-8 py-4 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors flex items-center justify-center gap-2 group shadow-lg shadow-orange-600/20">
            Start Now
            <span class="group-hover:translate-x-1 transition-transform">›</span>
          </button>

          <a href="#plans"
            class="px-8 py-4 bg-white/10 text-white rounded-xl hover:bg-white/15 transition-colors border border-white/15 flex items-center justify-center gap-2">
            See Memberships
            <span class="opacity-80">↓</span>
          </a>
        </div>

        <div class="mt-10 grid grid-cols-3 gap-4 max-w-lg">
          <div class="rounded-2xl bg-white/10 border border-white/15 p-4 hover:bg-white/12 transition">
            <p class="text-2xl font-extrabold">24/7</p>
            <p class="text-sm text-gray-200/80 mt-1">Access</p>
          </div>
          <div class="rounded-2xl bg-white/10 border border-white/15 p-4 hover:bg-white/12 transition">
            <p class="text-2xl font-extrabold">12+</p>
            <p class="text-sm text-gray-200/80 mt-1">Trainers</p>
          </div>
          <div class="rounded-2xl bg-white/10 border border-white/15 p-4 hover:bg-white/12 transition">
            <p class="text-2xl font-extrabold">4.9★</p>
            <p class="text-sm text-gray-200/80 mt-1">Rating</p>
          </div>
        </div>
      </div>

      <div class="space-y-4 reveal">
        <div class="bg-white/10 border border-white/15 rounded-3xl p-6 backdrop-blur hover:bg-white/12 transition shadow-2xl shadow-black/20">
          <p class="text-orange-400 font-semibold text-sm">Today’s Program</p>
          <p class="text-white text-2xl font-extrabold mt-1">Strength + HIIT</p>
          <p class="text-gray-200/90 mt-2">Burn calories, build power, improve endurance — in one session.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div class="bg-white/10 border border-white/15 rounded-3xl p-6 backdrop-blur hover:bg-white/12 transition shadow-2xl shadow-black/20 hover:-translate-y-1">
            <div class="text-2xl">🏋️</div>
            <p class="text-white font-bold mt-2">Free Weights Zone</p>
            <p class="text-gray-200/90 text-sm mt-1">Squat racks, benches, plates, dumbbells.</p>
          </div>
          <div class="bg-white/10 border border-white/15 rounded-3xl p-6 backdrop-blur hover:bg-white/12 transition shadow-2xl shadow-black/20 hover:-translate-y-1">
            <div class="text-2xl">🧑‍🏫</div>
            <p class="text-white font-bold mt-2">Personal Coaching</p>
            <p class="text-gray-200/90 text-sm mt-1">Form correction + weekly planning.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<section class="bg-gray-950">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
  </div>
</section>

<section class="relative py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 overflow-hidden">
  <div class="absolute inset-0 opacity-[0.05] pointer-events-none"
       style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.35) 1px, transparent 0);
              background-size: 24px 24px;"></div>

  <div class="relative max-w-7xl mx-auto">
    <div class="text-center mb-12 reveal">
      <p class="text-orange-400 font-semibold tracking-wide">WHY BRAVESTATION</p>
      <h2 class="text-4xl md:text-5xl font-extrabold mt-2 text-white">Everything you need, in one place</h2>
      <p class="text-lg text-gray-200/80 mt-4 max-w-2xl mx-auto">
        Clean design, real gym features. Built for consistency and progress.
      </p>
    </div>

<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">

  <div class="group rounded-3xl bg-white/10 border border-white/15 backdrop-blur
              transition shadow-2xl shadow-black/20 hover:-translate-y-1 hover:bg-white/12 reveal overflow-hidden">

    <div class="relative h-40">
      <img src="https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=800&q=80"
           class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition"
           alt="Premium Equipment">
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
    </div>

    <div class="p-6">
      <h3 class="font-bold text-lg text-white mb-2">Premium Equipment</h3>
      <p class="text-gray-200/80">
        Machines, cables, benches, and serious free weights.
      </p>
    </div>
  </div>

  <div class="group rounded-3xl bg-white/10 border border-white/15 backdrop-blur
              transition shadow-2xl shadow-black/20 hover:-translate-y-1 hover:bg-white/12 reveal overflow-hidden">

    <div class="relative h-40">
      <img src="https://images.unsplash.com/photo-1599058917212-d750089bc07e?auto=format&fit=crop&w=800&q=80"
           class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition"
           alt="HIIT Training">
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
    </div>

    <div class="p-6">
      <h3 class="font-bold text-lg text-white mb-2">HIIT & Conditioning</h3>
      <p class="text-gray-200/80">
        Short, intense sessions for fat loss and stamina.
      </p>
    </div>
  </div>

  <div class="group rounded-3xl bg-white/10 border border-white/15 backdrop-blur
              transition shadow-2xl shadow-black/20 hover:-translate-y-1 hover:bg-white/12 reveal overflow-hidden">

    <div class="relative h-40">
      <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80"
           class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition"
           alt="Yoga and Mobility">
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
    </div>

    <div class="p-6">
      <h3 class="font-bold text-lg text-white mb-2">Mobility & Yoga</h3>
      <p class="text-gray-200/80">
        Recover better, move better, train longer.
      </p>
    </div>
  </div>

  <div class="group rounded-3xl bg-white/10 border border-white/15 backdrop-blur
              transition shadow-2xl shadow-black/20 hover:-translate-y-1 hover:bg-white/12 reveal overflow-hidden">

    <div class="relative h-40">
      <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80"
           class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition"
           alt="Track Progress">
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
    </div>

    <div class="p-6">
      <h3 class="font-bold text-lg text-white mb-2">Track Your History</h3>
      <p class="text-gray-200/80">
        Purchases and plans saved in your account.
      </p>
    </div>
  </div>

</div>


<section id="plans" class="relative py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 overflow-hidden">
  <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-[640px] h-24 bg-orange-500/10 blur-3xl rounded-full"></div>

  <div class="relative max-w-7xl mx-auto">
    <div class="text-center mb-10 reveal">
      <p class="text-orange-400 font-semibold tracking-wide">MEMBERSHIPS</p>
      <h2 class="text-4xl md:text-5xl font-extrabold mt-2 text-white">Choose your plan</h2>
      <p class="text-lg text-gray-200/80 mt-4 max-w-2xl mx-auto">
        Upgrade or downgrade anytime. No hidden fees.
      </p>

      <div class="mt-8 inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-2xl p-1">
        <button id="billMonthly" class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/15 text-white">Monthly</button>
        <button id="billYearly" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-200/80 hover:text-white">Yearly <span class="text-orange-400">(save 15%)</span></button>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <?php
        $plans = [
          ['name'=>'Basic','price'=>29,'features'=>['Gym Access','Locker Room','Basic Equipment'],'featured'=>false],
          ['name'=>'Premium','price'=>59,'features'=>['Everything in Basic','Group Classes','Nutrition Guide','Mobile App'],'featured'=>true],
          ['name'=>'Elite','price'=>99,'features'=>['Everything in Premium','Personal Trainer','Priority Booking','Spa Access'],'featured'=>false],
        ];
        foreach ($plans as $p):
          $isFeatured = $p['featured'];
      ?>
        <div class="group relative rounded-3xl p-8 border backdrop-blur shadow-2xl shadow-black/25 transition reveal
          <?= $isFeatured ? 'bg-white/12 border-orange-400/40 ring-2 ring-orange-500/70' : 'bg-white/10 border-white/15 hover:bg-white/12' ?>">
          
          <div class="absolute pointer-events-none opacity-0 group-hover:opacity-100 transition inset-0 rounded-3xl"
               style="box-shadow: 0 0 0 1px rgba(249,115,22,.25), 0 20px 60px rgba(0,0,0,.35);"></div>

          <?php if ($isFeatured): ?>
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-orange-600 text-white text-xs font-bold shadow">
              MOST POPULAR
            </div>
          <?php endif; ?>

          <div class="relative flex items-baseline justify-between">
            <h3 class="text-2xl font-extrabold text-white"><?= e($p['name']) ?></h3>

            <div class="text-right">
              <span class="text-4xl font-extrabold text-white planPrice"
                    data-month="<?= e((string)$p['price']) ?>"
                    data-year="<?= e((string)round($p['price'] * 12 * 0.85)) ?>">
                $<?= e((string)$p['price']) ?>
              </span>
              <span class="text-gray-200/70 planSuffix">/mo</span>
            </div>
          </div>

          <div class="relative mt-6 space-y-3">
            <?php foreach ($p['features'] as $f): ?>
              <div class="flex items-start gap-3 text-gray-200/85">
                <div class="w-6 h-6 rounded-full bg-green-500/15 text-green-300 flex items-center justify-center flex-shrink-0 border border-green-400/20">✓</div>
                <div><?= e($f) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="relative mt-8">
            <button
              class="w-full py-3 rounded-2xl font-semibold transition-colors
                <?= $isFeatured
                  ? 'bg-orange-600 text-white hover:bg-orange-700 shadow-lg shadow-orange-600/20'
                  : 'bg-white/10 text-white hover:bg-white/15 border border-white/15'
                ?>"
              data-open-login>
              Get Started
            </button>

            <p class="text-xs text-gray-200/60 mt-3 text-center">
              Cancel anytime • No hidden fees
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
(function () {
  const track = document.getElementById('heroCarousel');
  if (!track) return;

  const slides = Array.from(track.children);
  const dots = Array.from(document.querySelectorAll('.carDot'));
  const prev = document.getElementById('carPrev');
  const next = document.getElementById('carNext');

  let idx = 0;
  let timer = null;

  function setActiveDot() {
    dots.forEach((d, i) => {
      d.classList.toggle('bg-white/70', i === idx);
      d.classList.toggle('bg-white/25', i !== idx);
    });
  }

  function go(i) {
    idx = (i + slides.length) % slides.length;
    track.style.transform = `translateX(-${idx * 100}%)`;
    setActiveDot();
  }

  function start() {
    stop();
    timer = setInterval(() => go(idx + 1), 4500);
  }

  function stop() {
    if (timer) clearInterval(timer);
    timer = null;
  }

  prev && prev.addEventListener('click', () => { go(idx - 1); start(); });
  next && next.addEventListener('click', () => { go(idx + 1); start(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { go(i); start(); }));

  track.parentElement.addEventListener('mouseenter', stop);
  track.parentElement.addEventListener('mouseleave', start);

  go(0);
  start();
})();
</script>

<script>
document.querySelectorAll('[data-open-login]').forEach(btn => {
  btn.addEventListener('click', () => {
    const b1 = document.getElementById('openLogin') || document.getElementById('openLogin2');
    if (b1) b1.click();
  });
});
</script>

<script>
(function(){
  const nav = document.getElementById('topNav');
  if(!nav) return;

  function onScroll(){
    const sc = window.scrollY || document.documentElement.scrollTop;
    if(sc > 10){
      nav.classList.add('backdrop-blur','bg-gray-950/70','border-b','border-white/10');
    } else {
      nav.classList.remove('backdrop-blur','bg-gray-950/70','border-b','border-white/10');
    }
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();
</script>

<style>
  .reveal { opacity: 0; transform: translateY(14px); transition: opacity .7s ease, transform .7s ease; }
  .reveal.is-in { opacity: 1; transform: translateY(0); }
</style>
<script>
(function(){
  const els = Array.from(document.querySelectorAll('.reveal'));
  if(!els.length) return;

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if(e.isIntersecting){
        e.target.classList.add('is-in');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  els.forEach(el => io.observe(el));
})();
</script>

<script>
(function(){
  const mBtn = document.getElementById('billMonthly');
  const yBtn = document.getElementById('billYearly');
  const prices = Array.from(document.querySelectorAll('.planPrice'));
  const suffix = Array.from(document.querySelectorAll('.planSuffix'));

  if(!mBtn || !yBtn || !prices.length) return;

  function setMode(mode){
    if(mode === 'year'){
      mBtn.classList.remove('bg-white/15','text-white');
      mBtn.classList.add('text-gray-200/80');
      yBtn.classList.add('bg-white/15','text-white');
      yBtn.classList.remove('text-gray-200/80');

      prices.forEach(p => p.textContent = '$' + (p.dataset.year || p.dataset.month));
      suffix.forEach(s => s.textContent = '/yr');
    } else {
      yBtn.classList.remove('bg-white/15','text-white');
      yBtn.classList.add('text-gray-200/80');
      mBtn.classList.add('bg-white/15','text-white');
      mBtn.classList.remove('text-gray-200/80');

      prices.forEach(p => p.textContent = '$' + (p.dataset.month || p.dataset.year));
      suffix.forEach(s => s.textContent = '/mo');
    }
  }

  mBtn.addEventListener('click', () => setMode('month'));
  yBtn.addEventListener('click', () => setMode('year'));
  setMode('month');
})();
</script>

<footer class="bg-gray-950 text-white py-12 px-4 sm:px-6 lg:px-8 border-t border-white/10">
  <div class="max-w-7xl mx-auto text-center">
    <div class="flex items-center justify-center gap-2 mb-4">
      <div class="w-8 h-8 rounded-lg bg-orange-600 flex items-center justify-center text-white font-bold">B</div>
      <span class="font-bold text-xl">BraveStation</span>
    </div>
    <p class="text-gray-400">© 2025 BraveStation. All rights reserved.</p>
  </div>
</footer>


  <?php if (!is_logged_in()): ?>
  <div id="loginModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-8 relative">
      <button id="closeLogin" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>

      <h2 id="modalTitle" class="text-3xl font-bold mb-2">Welcome Back</h2>
      <p id="modalSub" class="text-gray-600 mb-8">Sign in to access your account</p>

      <?php
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['login_error'])):
      ?>
        <div class="mb-4 p-3 rounded-lg border bg-red-50 border-red-200 text-red-800">
          <?= e($_SESSION['login_error']) ?>
        </div>
      <?php
          unset($_SESSION['login_error']);
        endif;
      ?>

      <form id="authForm" method="post" action="login_action.php" class="space-y-4">
        <input type="hidden" name="mode" id="mode" value="signin" />

        <div id="nameWrap" class="hidden">
          <label class="block text-sm font-medium mb-2">Full Name</label>
          <input name="full_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-600" placeholder="John Doe">
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Email</label>
          <input name="email" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-600" placeholder="you@example.com" required>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Password</label>
          <input name="password" type="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-600" placeholder="••••••••" required>
        </div>

        <button id="submitBtn" type="submit" class="w-full py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
          Sign In
        </button>
      </form>

      <div class="mt-6 text-center">
        <button id="toggleMode" class="text-orange-600 hover:text-orange-700">
          Don't have an account? Sign Up
        </button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script>
    (function () {
      const params = new URLSearchParams(window.location.search);
      const shouldOpen = (params.get('login') === '1');

      const modal = document.getElementById('loginModal');
      if (!modal) return;

      if (!shouldOpen) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        return;
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');

      const mode = document.getElementById('mode');
      const nameWrap = document.getElementById('nameWrap');
      const submitBtn = document.getElementById('submitBtn');
      const modalTitle = document.getElementById('modalTitle');
      const modalSub = document.getElementById('modalSub');

      if (mode) mode.value = 'signin';
      if (nameWrap) nameWrap.classList.add('hidden');
      if (submitBtn) submitBtn.textContent = 'Sign In';
      if (modalTitle) modalTitle.textContent = 'Welcome Back';
      if (modalSub) modalSub.textContent = 'Sign in to access your account';

      params.delete('login');
      const newQs = params.toString();
      const newUrl = window.location.pathname + (newQs ? ('?' + newQs) : '');
      window.history.replaceState({}, '', newUrl);
    })();
  </script>

  <script src="assets/app.js"></script>
</body>
</html>


