<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';

require_login();
require_admin();

$admin = user();

$membershipPlans = [
  'Basic'   => 29,
  'Premium' => 59,
  'Elite'   => 99,
];

function yearlyPrice(int $monthly): int {
  return (int) round($monthly * 12 * 0.85); 
}

$st = db()->prepare("
  SELECT
    COALESCE(SUM(CASE WHEN billing_cycle='monthly' THEN price ELSE 0 END), 0) AS monthly_revenue,
    COALESCE(SUM(CASE WHEN billing_cycle='yearly'  THEN price ELSE 0 END), 0) AS yearly_revenue,
    COUNT(*) AS total_orders,
    COALESCE(SUM(CASE WHEN status='active' THEN 1 ELSE 0 END), 0) AS active_orders,
    COALESCE(SUM(price),0) AS total_revenue
  FROM orders
");
$st->execute();
$stats = $st->get_result()->fetch_assoc();

$monthlyRevenue = (int)($stats['monthly_revenue'] ?? 0);
$yearlyRevenue  = (int)($stats['yearly_revenue'] ?? 0);
$totalOrders    = (int)($stats['total_orders'] ?? 0);
$activeOrders   = (int)($stats['active_orders'] ?? 0);
$totalRevenue   = (int)($stats['total_revenue'] ?? 0);


$activeRows = [];
$rAct = db()->query("
  SELECT membership_name, billing_cycle, COUNT(*) AS cnt
  FROM orders
  WHERE status='active'
  GROUP BY membership_name, billing_cycle
  ORDER BY membership_name ASC, billing_cycle ASC
");
while ($rAct && ($row = $rAct->fetch_assoc())) $activeRows[] = $row;


$recentRows = [];
$rRec = db()->query("
  SELECT o.order_code, o.membership_name, o.billing_cycle, o.price, o.status, o.purchased_at, u.email
  FROM orders o
  JOIN users u ON u.id = o.user_id
  ORDER BY o.purchased_at DESC, o.id DESC
  LIMIT 6
");
while ($rRec && ($row = $rRec->fetch_assoc())) $recentRows[] = $row;


$topPlan = null;
$rTop = db()->query("
  SELECT membership_name, COALESCE(SUM(price),0) AS rev
  FROM orders
  GROUP BY membership_name
  ORDER BY rev DESC
  LIMIT 1
");
if ($rTop) $topPlan = $rTop->fetch_assoc();


$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if ($action === 'save_user') {
    $uid  = (int)($_POST['user_id'] ?? 0);
    $name = trim($_POST['full_name'] ?? '');
    $role = (($_POST['role'] ?? 'user') === 'admin') ? 'admin' : 'user';

    $newMembership = $_POST['membership'] ?? '';
    $oldMembership = $_POST['old_membership'] ?? '';

    $newCycle = (($_POST['billing_cycle'] ?? 'monthly') === 'yearly') ? 'yearly' : 'monthly';
    $oldCycle = (($_POST['old_billing_cycle'] ?? 'monthly') === 'yearly') ? 'yearly' : 'monthly';

    if ($uid > 0 && $name !== '') {
      $st = db()->prepare("UPDATE users SET full_name=?, role=? WHERE id=?");
      $st->bind_param("ssi", $name, $role, $uid);
      $st->execute();

      $membershipValid = ($newMembership !== '' && isset($membershipPlans[$newMembership]));
      $changed = ($newMembership !== $oldMembership) || ($newCycle !== $oldCycle);

      if ($membershipValid && $changed) {
        $st0 = db()->prepare("SELECT id FROM orders WHERE user_id=? ORDER BY purchased_at DESC, id DESC LIMIT 1");
        $st0->bind_param("i", $uid);
        $st0->execute();
        $o = $st0->get_result()->fetch_assoc();

        $monthly = (int)$membershipPlans[$newMembership];
        $price   = ($newCycle === 'yearly') ? yearlyPrice($monthly) : $monthly;
        $now     = date('Y-m-d H:i:s');

  
        $st2 = db()->prepare("UPDATE orders SET status='completed' WHERE user_id=? AND status='active'");
        $st2->bind_param("i", $uid);
        $st2->execute();

        if ($o) {
          $oid = (int)$o['id'];
          $st3 = db()->prepare("UPDATE orders
                                SET membership_name=?, price=?, billing_cycle=?, status='active', purchased_at=?
                                WHERE id=?");
          $st3->bind_param("sissi", $newMembership, $price, $newCycle, $now, $oid);
          $st3->execute();
        } else {
          $orderCode = 'ORD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
          $st4 = db()->prepare("INSERT INTO orders (user_id, order_code, membership_name, price, billing_cycle, status, purchased_at)
                                VALUES (?,?,?,?,?, 'active', ?)");
          $st4->bind_param("ississ", $uid, $orderCode, $newMembership, $price, $newCycle, $now);
          $st4->execute();
        }
      }
    }

    header('Location: admin.php'); exit;
  }

  if ($action === 'toggle_membership') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid > 0) {
      $st = db()->prepare("SELECT id, status FROM orders WHERE user_id=? ORDER BY purchased_at DESC, id DESC LIMIT 1");
      $st->bind_param("i", $uid);
      $st->execute();
      $o = $st->get_result()->fetch_assoc();

      if ($o) {
        $oid = (int)$o['id'];
        $newStatus = ($o['status'] === 'active') ? 'completed' : 'active';

        if ($newStatus === 'active') {
          $st2 = db()->prepare("UPDATE orders SET status='completed' WHERE user_id=? AND status='active'");
          $st2->bind_param("i", $uid);
          $st2->execute();
        }

        $st3 = db()->prepare("UPDATE orders SET status=? WHERE id=?");
        $st3->bind_param("si", $newStatus, $oid);
        $st3->execute();
      }
    }
    header('Location: admin.php'); exit;
  }

  if ($action === 'delete_user') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid > 0 && $uid !== (int)$admin['id']) {
      $st = db()->prepare("DELETE FROM users WHERE id=?");
      $st->bind_param("i", $uid);
      $st->execute();
    }
    header('Location: admin.php'); exit;
  }
}


$users = [];
$sql = "
  SELECT
    u.id,
    u.full_name,
    u.email,
    u.role,
    u.created_at,
    o.membership_name,
    o.billing_cycle,
    o.status AS membership_status
  FROM users u
  LEFT JOIN (
    SELECT o1.*
    FROM orders o1
    INNER JOIN (
      SELECT user_id, MAX(purchased_at) AS max_purchased
      FROM orders
      GROUP BY user_id
    ) last ON last.user_id = o1.user_id AND last.max_purchased = o1.purchased_at
  ) o ON o.user_id = u.id
  ORDER BY u.id DESC
";
$r = db()->query($sql);
while ($row = $r->fetch_assoc()) $users[] = $row;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin | BraveStation</title>
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

<header class="bg-gray-950/70 backdrop-blur border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

     
      <a href="index.php" class="flex items-center gap-3 shrink-0 group">
        <img src="assets/images/logo.jpeg"
             alt="BraveStation Logo"
             class="w-10 h-10 rounded-xl object-cover ring-1 ring-gray-200 group-hover:ring-orange-500/40 transition" />
        <div class="leading-tight">
          <span class="font-extrabold text-lg group-hover:text-orange-600 transition">
            BraveStation
          </span>
          <p class="text-xs text-gray-500 -mt-0.5">Admin Panel</p>
        </div>
      </a>

      <div class="flex items-center gap-3 shrink-0">
        <span class="hidden sm:inline text-sm font-medium text-gray-200/90 whitespace-nowrap">
  <?= e($admin['email']) ?>
</span>


        <a href="logout.php"
           class="px-4 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-700 transition shadow">
          Logout
        </a>
      </div>

    </div>
  </div>
</header>


<main class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6 reveal">
    <div>
      <h1 class="text-3xl md:text-4xl font-extrabold">Admin Overview</h1>
      <p class="text-gray-200/70">Revenue + active memberships + management.</p>
    </div>

    <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-2xl p-1">
      <button id="adminBillMonthly" class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/15 text-white">Monthly</button>
      <button id="adminBillYearly" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-200/80 hover:text-white">Yearly</button>
    </div>
  </div>

  <div class="grid md:grid-cols-4 gap-4 mb-10 reveal">
    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25">
      <p class="text-gray-200/70 text-sm">Revenue</p>
      <div class="mt-2 flex items-baseline gap-2">
        <div class="text-3xl font-extrabold">$<span class="adminMoney" data-month="<?= (int)$monthlyRevenue ?>" data-year="<?= (int)$yearlyRevenue ?>"><?= (int)$monthlyRevenue ?></span></div>
        <div class="adminSuffix text-gray-200/60 font-semibold">/month</div>
      </div>
      <p class="text-xs text-gray-200/60 mt-2">Monthly: <b>$<?= (int)$monthlyRevenue ?></b> • Yearly: <b>$<?= (int)$yearlyRevenue ?></b></p>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25">
      <p class="text-gray-200/70 text-sm">Total Revenue</p>
      <div class="mt-2 text-3xl font-extrabold">$<?= (int)$totalRevenue ?></div>
      <p class="text-xs text-gray-200/60 mt-2">All time (monthly + yearly)</p>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25">
      <p class="text-gray-200/70 text-sm">Active Memberships</p>
      <div class="mt-2 text-3xl font-extrabold"><?= (int)$activeOrders ?></div>
      <p class="text-xs text-gray-200/60 mt-2">Status = active</p>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-6 shadow-2xl shadow-black/25">
      <p class="text-gray-200/70 text-sm">Top Plan</p>
      <div class="mt-2 text-xl font-extrabold"><?= e($topPlan['membership_name'] ?? '—') ?></div>
      <p class="text-xs text-gray-200/60 mt-2">Revenue: $<?= (int)($topPlan['rev'] ?? 0) ?></p>
    </div>
  </div>

  <section class="grid lg:grid-cols-2 gap-6 mb-10 reveal">

    
    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-7 shadow-2xl shadow-black/25">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-extrabold">Active Memberships by Plan</h2>
        <span class="text-xs text-gray-200/60">Only status=active</span>
      </div>

      <?php if (!$activeRows): ?>
        <div class="text-gray-200/70 text-sm">No active memberships.</div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($activeRows as $ar): ?>
            <?php
              $cycle = ($ar['billing_cycle'] === 'yearly') ? 'yearly' : 'monthly';
              $pill = ($cycle === 'yearly')
                ? 'bg-orange-600/20 text-orange-300 border border-orange-500/30'
                : 'bg-white/10 text-gray-200/80 border border-white/15';
            ?>
            <div class="flex items-center justify-between rounded-2xl bg-white/5 border border-white/10 px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="font-semibold"><?= e($ar['membership_name']) ?></div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $pill ?>"><?= e($cycle) ?></span>
              </div>
              <div class="text-lg font-extrabold"><?= (int)$ar['cnt'] ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur p-7 shadow-2xl shadow-black/25">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-extrabold">Recent Purchases</h2>
        <span class="text-xs text-gray-200/60">Last 6</span>
      </div>

      <?php if (!$recentRows): ?>
        <div class="text-gray-200/70 text-sm">No orders yet.</div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($recentRows as $rr): ?>
            <?php
              $cycle = ($rr['billing_cycle'] === 'yearly') ? 'yearly' : 'monthly';
              $pill = ($rr['status'] === 'active')
                ? 'bg-green-500/15 text-green-300 border border-green-400/20'
                : 'bg-white/10 text-gray-200/70 border border-white/15';
            ?>
            <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold truncate"><?= e($rr['email']) ?></div>
                  <div class="text-sm text-gray-200/70">
                    <?= e($rr['membership_name']) ?> • <?= e($cycle) ?>
                  </div>
                  <div class="text-xs text-gray-200/60 mt-1">
                    <?= e(date('Y-m-d H:i', strtotime($rr['purchased_at']))) ?> •
                    <span class="font-mono"><?= e($rr['order_code']) ?></span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-extrabold text-orange-400">$<?= (int)$rr['price'] ?></div>
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?= $pill ?>"><?= e($rr['status']) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </section>

 
<section class="reveal">
  <div class="mb-4">
    <h2 class="text-2xl font-extrabold">User Management</h2>
    <p class="text-gray-200/70 text-sm">Edit fields then Save. Toggle/Delete are separate forms .</p>
  </div>

  <div class="rounded-3xl bg-white/10 border border-white/15 backdrop-blur shadow-2xl shadow-black/25 overflow-hidden">
    <div class="divide-y divide-white/10">
      <?php foreach ($users as $u): ?>
        <?php
          $currentMembership = $u['membership_name'] ?? 'Basic';
          if (!isset($membershipPlans[$currentMembership])) $currentMembership = 'Basic';

          $currentCycle = (($u['billing_cycle'] ?? 'monthly') === 'yearly') ? 'yearly' : 'monthly';

          $statusRaw   = $u['membership_status'] ?? null;
          $statusLabel = ($statusRaw === 'active') ? 'active' : 'inactive';
          $statusPill  = ($statusLabel === 'active')
            ? 'bg-green-500/15 text-green-300 border border-green-400/20'
            : 'bg-white/10 text-gray-200/70 border border-white/15';

          $optStyle = 'style="color:#111;background:#fff"';
          $uidSafe  = (int)$u['id'];
        ?>

        <div class="p-4 sm:p-6">
          <form method="post" class="grid grid-cols-12 gap-3 items-center">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="user_id" value="<?= $uidSafe ?>">
            <input type="hidden" name="old_membership" value="<?= e($currentMembership) ?>">
            <input type="hidden" name="old_billing_cycle" value="<?= e($currentCycle) ?>">

            <div class="col-span-12 md:col-span-3">
              <label class="text-xs text-gray-200/60">Name</label>
              <input name="full_name"
                     value="<?= e($u['full_name']) ?>"
                     class="mt-1 w-full px-3 py-2 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-orange-600">
            </div>

            <div class="col-span-12 md:col-span-3">
              <label class="text-xs text-gray-200/60">Email</label>
              <div class="mt-1 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-gray-200/80 truncate">
                <?= e($u['email']) ?>
              </div>
            </div>

            <div class="col-span-6 md:col-span-1">
              <label class="text-xs text-gray-200/60">Role</label>
              <select name="role"
                      class="mt-1 w-full px-3 py-2 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-orange-600"
                      style="color-scheme: dark;">
                <option <?= $optStyle ?> value="user"  <?= $u['role']==='user'?'selected':'' ?>>user</option>
                <option <?= $optStyle ?> value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
              </select>
            </div>

            <div class="col-span-6 md:col-span-2">
              <label class="text-xs text-gray-200/60">Plan</label>
              <select name="membership"
                      class="mt-1 w-full px-3 py-2 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-orange-600"
                      style="color-scheme: dark;">
                <?php foreach ($membershipPlans as $planName => $planPrice): ?>
                  <option <?= $optStyle ?> value="<?= e($planName) ?>" <?= $planName===$currentMembership?'selected':'' ?>>
                    <?= e($planName) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-span-6 md:col-span-2">
              <label class="text-xs text-gray-200/60">Billing</label>
              <select name="billing_cycle"
                      class="mt-1 w-full min-w-[120px] px-3 py-2 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-orange-600"
                      style="color-scheme: dark;">
                <option <?= $optStyle ?> value="monthly" <?= $currentCycle==='monthly'?'selected':'' ?>>monthly</option>
                <option <?= $optStyle ?> value="yearly"  <?= $currentCycle==='yearly'?'selected':'' ?>>yearly</option>
              </select>
            </div>

            <div class="col-span-6 md:col-span-1">
              <label class="text-xs text-gray-200/60">Status</label>
              <div class="mt-1">
                <span class="inline-flex px-3 py-2 rounded-xl text-xs font-semibold <?= $statusPill ?>">
                  <?= e($statusLabel) ?>
                </span>
              </div>
            </div>

            <div class="col-span-6 md:col-span-2">
              <label class="text-xs text-gray-200/60">Join</label>
              <div class="mt-1 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-gray-200/80 text-sm">
                <?= e(date('Y-m-d', strtotime($u['created_at']))) ?>
              </div>
            </div>

            <div class="col-span-12 flex items-center justify-end gap-3 pt-2">
              <button type="submit"
                      class="px-4 py-2 rounded-xl bg-orange-600 text-white hover:bg-orange-700 transition shadow-lg shadow-orange-600/20">
                Save
              </button>
            </div>
          </form>

          <div class="flex items-center justify-end gap-3 mt-3">
            <form method="post" class="inline">
              <input type="hidden" name="action" value="toggle_membership">
              <input type="hidden" name="user_id" value="<?= $uidSafe ?>">
              <button title="Toggle Active/Inactive"
                      class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 hover:bg-white/15 transition grid place-items-center text-white">
                ↻
              </button>
            </form>

            <?php if ($uidSafe !== (int)$admin['id']): ?>
              <form method="post" class="inline" onsubmit="return confirm('Delete this user?')">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="<?= $uidSafe ?>">
                <button title="Delete"
                        class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 hover:bg-red-500/15 hover:border-red-400/30 transition grid place-items-center text-red-300">
                  🗑
                </button>
              </form>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

</main>

<script>
(function(){
  const mBtn = document.getElementById('adminBillMonthly');
  const yBtn = document.getElementById('adminBillYearly');
  const money = Array.from(document.querySelectorAll('.adminMoney'));
  const suffix = document.querySelector('.adminSuffix');
  if(!mBtn || !yBtn || !money.length) return;

  function setMode(mode){
    const isYear = (mode === 'year');
    if(isYear){
      mBtn.classList.remove('bg-white/15','text-white'); mBtn.classList.add('text-gray-200/80');
      yBtn.classList.add('bg-white/15','text-white'); yBtn.classList.remove('text-gray-200/80');
      money.forEach(el => el.textContent = (el.dataset.year || el.dataset.month));
      if(suffix) suffix.textContent = '/year';
    } else {
      yBtn.classList.remove('bg-white/15','text-white'); yBtn.classList.add('text-gray-200/80');
      mBtn.classList.add('bg-white/15','text-white'); mBtn.classList.remove('text-gray-200/80');
      money.forEach(el => el.textContent = (el.dataset.month || el.dataset.year));
      if(suffix) suffix.textContent = '/month';
    }
  }
  mBtn.addEventListener('click', () => setMode('month'));
  yBtn.addEventListener('click', () => setMode('year'));
  setMode('month');
})();
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

</body>
</html>
