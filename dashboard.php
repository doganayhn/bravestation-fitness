<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';

require_login();
if (is_admin()) { header('Location: admin.php'); exit; }

$u = user();

if (isset($_GET['ajax']) && $_GET['ajax'] === 'orders') {
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');

  $ordersAjax = [];
  $st = db()->prepare("SELECT order_code, membership_name, price, billing_cycle, status, purchased_at
                       FROM orders
                       WHERE user_id=?
                       ORDER BY id DESC");
  $st->bind_param("i", $u['id']);
  $st->execute();
  $res = $st->get_result();
  while ($row = $res->fetch_assoc()) $ordersAjax[] = $row;

  echo json_encode(['ok' => true, 'orders' => $ordersAjax], JSON_UNESCAPED_UNICODE);
  exit;
}

$trainers = [];
$r = db()->query("SELECT * FROM trainers ORDER BY id ASC");
while ($row = $r->fetch_assoc()) $trainers[] = $row;

$orders = [];
$st = db()->prepare("SELECT order_code, membership_name, price, billing_cycle, status, purchased_at
                     FROM orders WHERE user_id=? ORDER BY id DESC");
$st->bind_param("i", $u['id']);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) $orders[] = $row;

$memberships = [
  ['id'=>'basic','name'=>'Basic','price'=>29,'features'=>['Gym Access','Locker Room','Basic Equipment','Free Parking'],'featured'=>false],
  ['id'=>'premium','name'=>'Premium','price'=>59,'features'=>['Everything in Basic','Group Classes','Nutrition Guide','Mobile App','Steam & Sauna'],'featured'=>true],
  ['id'=>'elite','name'=>'Elite','price'=>99,'features'=>['Everything in Premium','Personal Trainer (2x/week)','Priority Booking','Spa Access','Free Merchandise'],'featured'=>false],
];

function yearlyPrice(int $monthly): int {
  return (int) round($monthly * 12 * 0.85); 
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard | BraveStation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .reveal{opacity:0;transform:translateY(14px);transition:opacity .7s ease,transform .7s ease}
    .reveal.is-in{opacity:1;transform:translateY(0)}
  </style>
</head>

<body class="min-h-screen bg-gray-950 text-white">

<div class="fixed inset-0 pointer-events-none">
  <div class="absolute inset-0 opacity-[0.05]"
       style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.35) 1px, transparent 0);
              background-size: 24px 24px;"></div>
  <div class="absolute -top-24 -left-24 w-[520px] h-[520px] bg-orange-500/10 blur-3xl rounded-full"></div>
  <div class="absolute -bottom-32 -right-24 w-[620px] h-[620px] bg-orange-500/10 blur-3xl rounded-full"></div>
</div>

<header id="topNav" class="sticky top-0 z-50 border-b border-white/10 bg-gray-950/70 backdrop-blur">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between">

      <div class="flex items-center min-w-0">
        <a href="index.php" class="flex items-center gap-3 group whitespace-nowrap">
          <img src="assets/images/logo.jpeg" alt="BraveStation Logo"
               class="w-10 h-10 rounded-xl object-cover ring-1 ring-white/10 group-hover:ring-orange-500/40 transition" />
          <div class="leading-tight">
            <span class="font-extrabold text-lg group-hover:text-orange-400 transition">BraveStation</span>
            <p class="text-xs text-gray-200/60 -mt-0.5">Member Dashboard</p>
          </div>
        </a>
      </div>

      <div class="flex items-center gap-3">
        <button id="openCart"
          class="relative w-11 h-11 rounded-xl bg-white/10 border border-white/15 hover:bg-white/15 transition grid place-items-center">
          🛒
          <span id="cartCount"
                class="absolute -top-1 -right-1 w-5 h-5 bg-orange-600 text-white text-xs rounded-full hidden items-center justify-center">0</span>
        </button>

        <span class="hidden sm:inline text-sm text-gray-200/80"><?= e($u['email']) ?></span>
        

        <a href="logout.php"
           class="px-4 py-2 rounded-xl bg-orange-600 text-white hover:bg-orange-700 transition shadow-lg shadow-orange-600/20">
          Logout
        </a>
      </div>

    </div>
  </div>
</header>


<div class="border-b border-white/10 bg-gray-950/40 backdrop-blur">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex gap-3 overflow-x-auto py-2">
      <button class="tabBtn px-4 py-2 rounded-xl border transition whitespace-nowrap bg-white/10 border-white/15 text-white" data-tab="membership">
        Membership Plans
      </button>
      <button class="tabBtn px-4 py-2 rounded-xl border transition whitespace-nowrap bg-transparent border-transparent text-gray-200/70 hover:text-white hover:bg-white/10 hover:border-white/15" data-tab="trainers">
        Our Trainers
      </button>
      <button class="tabBtn px-4 py-2 rounded-xl border transition whitespace-nowrap bg-transparent border-transparent text-gray-200/70 hover:text-white hover:bg-white/10 hover:border-white/15" data-tab="bookings">
        Book Classes
      </button>
      <button id="goOrdersTab" class="tabBtn px-4 py-2 rounded-xl border transition whitespace-nowrap bg-transparent border-transparent text-gray-200/70 hover:text-white hover:bg-white/10 hover:border-white/15" data-tab="orders">
        My Orders
      </button>
    </div>
  </div>
</div>

<main class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <section class="tabPanel reveal" id="tab-membership">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl md:text-4xl font-extrabold mb-2">Choose Your Membership</h2>
        <p class="text-gray-200/70">Monthly or yearly billing — your choice.</p>
      </div>

      <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-2xl p-1">
        <button id="billMonthly" class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/15 text-white">Monthly</button>
        <button id="billYearly" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-200/80 hover:text-white">
          Yearly <span class="text-orange-400">(save 15%)</span>
        </button>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($memberships as $m): ?>
        <?php
          $isFeatured = (bool)$m['featured'];
          $cardClass = $isFeatured
            ? 'bg-white/12 border-orange-400/40 ring-2 ring-orange-500/70'
            : 'bg-white/10 border-white/15 hover:bg-white/12';

          $btnClass = 'bg-white/10 hover:bg-white/15 border border-white/15';
          if ($m['id'] === 'premium') $btnClass = 'bg-orange-600 hover:bg-orange-700 border border-orange-500/40 shadow-lg shadow-orange-600/20';

          $monthly = (int)$m['price'];
          $yearly  = yearlyPrice($monthly);
        ?>
        <div class="group relative rounded-3xl p-7 border backdrop-blur shadow-2xl shadow-black/25 transition hover:-translate-y-1 <?= $cardClass ?>">
          <div class="absolute pointer-events-none opacity-0 group-hover:opacity-100 transition inset-0 rounded-3xl"
               style="box-shadow: 0 0 0 1px rgba(249,115,22,.25), 0 20px 60px rgba(0,0,0,.35);"></div>

          <?php if ($isFeatured): ?>
            <div class="relative inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-4 bg-orange-600 text-white shadow">
              <span class="w-2 h-2 rounded-full bg-white/90"></span> MOST POPULAR
            </div>
          <?php endif; ?>

          <h3 class="relative text-2xl font-extrabold mb-2"><?= e($m['name']) ?></h3>

          <div class="relative mb-6">
            <span class="text-4xl font-extrabold planPrice"
                  data-month="<?= (int)$monthly ?>"
                  data-year="<?= (int)$yearly ?>">
              $<?= (int)$monthly ?>
            </span>
            <span class="text-gray-200/70 planSuffix">/month</span>

            <div class="mt-2 text-xs text-gray-200/60">
              Yearly total: <span class="font-semibold text-gray-200/80">$<?= (int)$yearly ?></span>
            </div>
          </div>

          <ul class="relative space-y-3 mb-6">
            <?php foreach ($m['features'] as $f): ?>
              <li class="flex items-start gap-3 text-gray-200/85">
                <span class="w-6 h-6 rounded-full bg-green-500/15 text-green-300 grid place-items-center border border-green-400/20 flex-shrink-0">✓</span>
                <span><?= e($f) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>

          <button class="relative addToCart w-full py-3 rounded-2xl font-semibold text-white transition <?= $btnClass ?>"
            data-item-key="<?= e($m['id']) ?>"
            data-item-name="<?= e($m['name']) ?>"
            data-price="<?= (int)$monthly ?>"
            data-billing-cycle="monthly">
            Add to Cart
          </button>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="tabPanel hidden reveal" id="tab-trainers">
    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-extrabold mb-2">Meet Our Trainers</h2>
      <p class="text-gray-200/70">Expert coaches ready to guide your fitness journey.</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($trainers as $t): ?>
        <div class="group rounded-3xl overflow-hidden bg-white/10 border border-white/15 backdrop-blur shadow-2xl shadow-black/25 transition hover:-translate-y-1 hover:bg-white/12">
          <img src="<?= e($t['image_url']) ?>" alt="<?= e($t['name']) ?>" class="w-full h-64 object-cover" />
          <div class="p-6">
            <h3 class="text-xl font-extrabold mb-2"><?= e($t['name']) ?></h3>
            <div class="flex items-center gap-2 mb-3 text-gray-200/80">
              <span class="text-yellow-400">★</span>
              <span class="font-semibold"><?= e((string)$t['rating']) ?></span>
              <span class="text-gray-200/50 text-sm">(127 reviews)</span>
            </div>
            <div class="space-y-2 mb-4 text-sm text-gray-200/75">
              <div class="flex items-center gap-2"><span>🏅</span><span><?= e($t['specialty']) ?></span></div>
              <div class="flex items-center gap-2"><span>⏱</span><span><?= e($t['experience']) ?> experience</span></div>
            </div>
            <p class="text-gray-200/70 text-sm"><?= e($t['bio']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

<section class="tabPanel hidden reveal" id="tab-bookings">
  <div class="mb-8">
    <h2 class="text-3xl md:text-4xl font-extrabold mb-2 text-white">
      Book Your Classes
    </h2>
    <p class="text-gray-200/90">
      Reserve your spot with our expert trainers.
    </p>
  </div>

  <div class="grid lg:grid-cols-2 gap-8">
    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-7 shadow-2xl shadow-black/25">
      <h3 class="text-xl font-extrabold mb-6 text-white">
        Book a New Class
      </h3>

      <form id="bookingForm" class="space-y-4">

        <div>
          <label class="block font-medium mb-2 text-gray-100">
            Select Trainer
          </label>
          <select name="trainer_id"
            class="w-full px-4 py-3 rounded-2xl
                   bg-white/10 border border-white/15
                   text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-orange-600
                   [&>option]:bg-white [&>option]:text-gray-900"
            required>
            <option value="">Choose a trainer</option>
            <?php foreach ($trainers as $t): ?>
              <option value="<?= (int)$t['id'] ?>">
                <?= e($t['name']) ?> - <?= e($t['specialty']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-medium mb-2 text-gray-100">
            Class Type
          </label>
          <select name="class_type"
            class="w-full px-4 py-3 rounded-2xl
                   bg-white/10 border border-white/15
                   text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-orange-600
                   [&>option]:bg-white [&>option]:text-gray-900"
            required>
            <option value="">Choose class type</option>
            <?php foreach ([
              'Personal Training','Group Class','Yoga Session',
              'HIIT Workout','Strength Training','Cardio Blast'
            ] as $ct): ?>
              <option value="<?= e($ct) ?>"><?= e($ct) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-medium mb-2 text-gray-100">
            Date
          </label>
          <input name="date" type="date" min="<?= date('Y-m-d') ?>"
            class="w-full px-4 py-3 rounded-2xl
                   bg-white/10 border border-white/15
                   text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-orange-600"
            required />
        </div>

        <div>
          <label class="block font-medium mb-2 text-gray-100">
            Time Slot
          </label>
          <select name="time_slot"
            class="w-full px-4 py-3 rounded-2xl
                   bg-white/10 border border-white/15
                   text-gray-100
                   focus:outline-none focus:ring-2 focus:ring-orange-600
                   [&>option]:bg-white [&>option]:text-gray-900"
            required>
            <option value="">Choose a time</option>
            <?php foreach ([
              '6:00 AM','7:00 AM','8:00 AM','9:00 AM','10:00 AM',
              '11:00 AM','12:00 PM','1:00 PM','2:00 PM','3:00 PM',
              '4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM'
            ] as $ts): ?>
              <option value="<?= e($ts) ?>"><?= e($ts) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit"
          class="w-full py-3 rounded-2xl bg-orange-600 text-white
                 font-semibold hover:bg-orange-700 transition
                 shadow-lg shadow-orange-600/20">
          Book Class
        </button>
      </form>

      <div id="bookingSuccess"
           class="mt-4 px-4 py-3 rounded-2xl
                  bg-green-500/15 border border-green-400/20
                  text-green-200 hidden">
        ✓ Class booked successfully!
      </div>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-7 shadow-2xl shadow-black/25">
      <h3 class="text-xl font-extrabold mb-6 text-white">
        Your Bookings
      </h3>

      <div id="bookingList" class="space-y-4"></div>

      <div id="bookingEmpty" class="text-center py-12 text-gray-300/80">
        <div class="w-12 h-12 mx-auto mb-4 text-gray-200/30">📅</div>
        <p>No bookings yet. Book your first class!</p>
      </div>
    </div>
  </div>
</section>


  <section class="tabPanel hidden reveal" id="tab-orders">
    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-extrabold mb-2">Order History</h2>
      <p class="text-gray-200/70">Now includes monthly/yearly billing.</p>
    </div>

    <div id="ordersEmpty" class="<?= !$orders ? '' : 'hidden' ?>">
      <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-12 text-center shadow-2xl shadow-black/25">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-200/30">📦</div>
        <p class="text-gray-200/70">No orders yet</p>
        <p class="text-sm text-gray-200/50 mt-2">Purchase a membership to see it here</p>
      </div>
    </div>

    <div id="ordersList" class="space-y-4 <?= !$orders ? 'hidden' : '' ?>">
      <?php foreach ($orders as $o): ?>
        <?php
          $cycle = ($o['billing_cycle'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly';
          $pill = ($o['status'] === 'active')
            ? 'bg-green-500/15 text-green-300 border border-green-400/20'
            : 'bg-white/10 text-gray-200/70 border border-white/15';
        ?>
        <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25 hover:bg-white/12 transition">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-extrabold"><?= e($o['membership_name']) ?> Membership</h3>
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $pill ?>"><?= e($o['status']) ?></span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/15 text-gray-200/70">
                  <?= e($cycle) ?>
                </span>
              </div>
              <div class="text-sm text-gray-200/70">Purchased on <?= e(date('m/d/Y', strtotime($o['purchased_at']))) ?></div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-extrabold text-orange-400">$<?= e((string)$o['price']) ?></p>
              <p class="text-sm text-gray-200/60"><?= $cycle === 'yearly' ? 'per year' : 'per month' ?></p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-sm text-gray-200/70">
              Order ID: <span class="font-mono text-gray-200/85"><?= e($o['order_code']) ?></span>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<div id="cartOverlay" class="fixed inset-0 bg-black/60 z-50 hidden justify-end">
  <div class="w-full max-w-md h-full flex flex-col bg-gray-950/85 backdrop-blur border-l border-white/10">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="text-2xl font-extrabold">Your Cart</h2>
      <button id="closeCart" class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 hover:bg-white/15 transition grid place-items-center">✕</button>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
      <div id="cartEmpty" class="text-center py-12 text-gray-200/60 hidden">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-200/30">🛒</div>
        <p>Your cart is empty</p>
        <p class="text-sm mt-2 text-gray-200/50">Add a membership plan to get started</p>
      </div>
      <div id="cartItems" class="space-y-4"></div>
    </div>

    <div id="purchaseOk" class="py-3 bg-green-500/15 text-green-200 text-center rounded-2xl border border-green-400/20 hidden">
        ✓ Purchase Successful!
      </div>

    <div id="cartFooter" class="border-t border-white/10 p-6 space-y-4 hidden">
      <div class="flex items-center justify-between text-lg">
        <span class="font-medium text-gray-200/80">Total</span>
        <span class="font-extrabold text-white">$<span id="cartTotal">0</span><span id="cartCycleTxt" class="text-gray-200/70">/month</span></span>
      </div>

      <button id="checkoutBtn"
        class="w-full py-3 bg-orange-600 text-white rounded-2xl hover:bg-orange-700 transition-colors flex items-center justify-center gap-2 font-semibold shadow-lg shadow-orange-600/20">
        Complete Purchase
      </button>

      <p class="text-xs text-gray-200/50 text-center">No payment required for demo purposes</p>
    </div>
  </div>
</div>

<script>
(function(){
  const mBtn = document.getElementById('billMonthly');
  const yBtn = document.getElementById('billYearly');
  const prices = Array.from(document.querySelectorAll('.planPrice'));
  const suffix = Array.from(document.querySelectorAll('.planSuffix'));
  const buttons = Array.from(document.querySelectorAll('.addToCart'));
  const cartCycleTxt = document.getElementById('cartCycleTxt');

  if(!mBtn || !yBtn || !prices.length || !buttons.length) return;

  function setMode(mode){
    const isYear = (mode === 'year');

    if(isYear){
      mBtn.classList.remove('bg-white/15','text-white'); mBtn.classList.add('text-gray-200/80');
      yBtn.classList.add('bg-white/15','text-white'); yBtn.classList.remove('text-gray-200/80');

      prices.forEach(p => p.textContent = '$' + (p.dataset.year || p.dataset.month));
      suffix.forEach(s => s.textContent = '/year');

      buttons.forEach((b) => {
        const cardPrice = b.closest('.group')?.querySelector('.planPrice');
        const year = cardPrice?.dataset.year || b.dataset.price;
        b.dataset.price = year;
        b.dataset.billingCycle = 'yearly'; 
      });

      if(cartCycleTxt) cartCycleTxt.textContent = '/year';
    } else {
      yBtn.classList.remove('bg-white/15','text-white'); yBtn.classList.add('text-gray-200/80');
      mBtn.classList.add('bg-white/15','text-white'); mBtn.classList.remove('text-gray-200/80');

      prices.forEach(p => p.textContent = '$' + (p.dataset.month || p.dataset.year));
      suffix.forEach(s => s.textContent = '/month');

      buttons.forEach((b) => {
        const cardPrice = b.closest('.group')?.querySelector('.planPrice');
        const month = cardPrice?.dataset.month || b.dataset.price;
        b.dataset.price = month;
        b.dataset.billingCycle = 'monthly'; 
      });

      if(cartCycleTxt) cartCycleTxt.textContent = '/month';
    }
  }

  mBtn.addEventListener('click', () => setMode('month'));
  yBtn.addEventListener('click', () => setMode('year'));
  setMode('month');
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ordersTabBtn = document.getElementById('goOrdersTab');
  const ordersEmpty  = document.getElementById('ordersEmpty');
  const ordersList   = document.getElementById('ordersList');
  let cachedOrders = null;

  function esc(s) {
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function renderOrders(orders) {
    if (!ordersEmpty || !ordersList) return;
    if (!orders || orders.length === 0) {
      ordersList.innerHTML = '';
      ordersList.classList.add('hidden');
      ordersEmpty.classList.remove('hidden');
      return;
    }
    ordersEmpty.classList.add('hidden');
    ordersList.classList.remove('hidden');

    ordersList.innerHTML = orders.map(o => {
      const cycle = (o.billing_cycle === 'yearly') ? 'yearly' : 'monthly';
      const pill = (o.status === 'active')
        ? 'bg-green-500/15 text-green-300 border border-green-400/20'
        : 'bg-white/10 text-gray-200/70 border border-white/15';

      const d = o.purchased_at ? new Date(String(o.purchased_at).replace(' ', 'T')) : null;
      const dateTxt = (d && !isNaN(d))
        ? String(d.getMonth()+1).padStart(2,'0') + '/' + String(d.getDate()).padStart(2,'0') + '/' + d.getFullYear()
        : '';

      return `
        <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25 hover:bg-white/12 transition">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-extrabold">${esc(o.membership_name)} Membership</h3>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${pill}">${esc(o.status)}</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/15 text-gray-200/70">${cycle}</span>
              </div>
              <div class="text-sm text-gray-200/70">Purchased on ${esc(dateTxt)}</div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-extrabold text-orange-400">$${esc(String(o.price))}</p>
              <p class="text-sm text-gray-200/60">${cycle === 'yearly' ? 'per year' : 'per month'}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-sm text-gray-200/70">
              Order ID: <span class="font-mono text-gray-200/85">${esc(o.order_code)}</span>
            </p>
          </div>
        </div>
      `;
    }).join('');
  }

  async function fetchOrders() {
    const res = await fetch('dashboard.php?ajax=orders', { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
    const data = await res.json();
    if (data && data.ok) {
      cachedOrders = data.orders || [];
      return cachedOrders;
    }
    return null;
  }

  if (ordersTabBtn) {
    ordersTabBtn.addEventListener('click', async () => {
      if (cachedOrders !== null) {
        renderOrders(cachedOrders);
        fetchOrders().then(o => o && renderOrders(o)).catch(()=>{});
      } else {
        const o = await fetchOrders().catch(()=>null);
        if (o) renderOrders(o);
      }
    });
  }
});
</script>

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

<script src="assets/app.js?v=10"></script>
</body>
</html>
