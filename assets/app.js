
(function () {
  
  if (document.getElementById("topWarnBar")) return;

  const bar = document.createElement("div");
  bar.id = "topWarnBar";
  bar.className =
    "fixed top-3 left-1/2 -translate-x-1/2 z-[9999] hidden " +
    "max-w-3xl w-[calc(100%-24px)] " +
    "px-4 py-3 rounded-2xl " +
    "bg-red-500/15 border border-red-400/25 text-red-100 backdrop-blur " +
    "shadow-2xl shadow-black/30";

  bar.innerHTML = `
    <div class="flex items-start justify-between gap-3">
      <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-red-500/20 border border-red-400/20 grid place-items-center">⚠️</div>
        <div>
          <div class="font-semibold" id="topWarnTitle">Warning</div>
          <div class="text-sm text-red-100/80" id="topWarnMsg">Message</div>
        </div>
      </div>
      <button id="topWarnClose" class="w-9 h-9 rounded-xl bg-white/10 border border-white/10 hover:bg-white/15 transition grid place-items-center">✕</button>
    </div>
  `;
  document.body.appendChild(bar);

  document.getElementById("topWarnClose")?.addEventListener("click", () => {
    bar.classList.add("hidden");
  });

  window.showTopWarning = function (msg, title = "Warning") {
    const t = document.getElementById("topWarnTitle");
    const m = document.getElementById("topWarnMsg");
    if (t) t.textContent = title;
    if (m) m.textContent = msg || "Warning";
    bar.classList.remove("hidden");
    clearTimeout(window.__topWarnTimer);
    window.__topWarnTimer = setTimeout(() => bar.classList.add("hidden"), 4500);
  };
})();


(function () {
  const modal = document.getElementById("loginModal");
  if (!modal) return;

  const openBtns = [
    document.getElementById("openLogin"),
    document.getElementById("openLogin2"),
    ...document.querySelectorAll("[data-open-login]"),
  ].filter(Boolean);

  const closeBtn = document.getElementById("closeLogin");
  const toggleBtn = document.getElementById("toggleMode");
  const modeEl = document.getElementById("mode");
  const nameWrap = document.getElementById("nameWrap");
  const title = document.getElementById("modalTitle");
  const sub = document.getElementById("modalSub");
  const submit = document.getElementById("submitBtn");

  function open() {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }
  function close() {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }

  openBtns.forEach((b) => b.addEventListener("click", open));
  closeBtn && closeBtn.addEventListener("click", close);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) close();
  });

  
  if (toggleBtn && modeEl) {
    toggleBtn.addEventListener("click", () => {
      const isSignUp = modeEl.value === "signin";
      modeEl.value = isSignUp ? "signup" : "signin";

      if (modeEl.value === "signup") {
        nameWrap && nameWrap.classList.remove("hidden");
        if (title) title.textContent = "Create Account";
        if (sub) sub.textContent = "Start your fitness journey today";
        if (submit) submit.textContent = "Sign Up";
        toggleBtn.textContent = "Already have an account? Sign In";
      } else {
        nameWrap && nameWrap.classList.add("hidden");
        if (title) title.textContent = "Welcome Back";
        if (sub) sub.textContent = "Sign in to access your account";
        if (submit) submit.textContent = "Sign In";
        toggleBtn.textContent = "Don't have an account? Sign Up";
      }
    });
  }
})();


(function () {
  const tabBtns = document.querySelectorAll(".tabBtn");
  if (!tabBtns.length) return;

  function setTab(name) {
    document.querySelectorAll(".tabPanel").forEach((p) => p.classList.add("hidden"));
    document.getElementById("tab-" + name)?.classList.remove("hidden");

    tabBtns.forEach((b) => {
      const active = b.dataset.tab === name;
      b.className =
        "tabBtn px-4 py-2 rounded-xl border transition whitespace-nowrap " +
        (active
          ? "bg-white/10 border-white/15 text-white"
          : "bg-transparent border-transparent text-gray-200/70 hover:text-white hover:bg-white/10 hover:border-white/15");
    });
  }

  tabBtns.forEach((b) => b.addEventListener("click", () => setTab(b.dataset.tab)));
})();


(async function () {
  const overlay = document.getElementById("cartOverlay");
  if (!overlay) return;

  const openCart = document.getElementById("openCart");
  const closeCart = document.getElementById("closeCart");
  const itemsWrap = document.getElementById("cartItems");
  const empty = document.getElementById("cartEmpty");
  const footer = document.getElementById("cartFooter");
  const totalEl = document.getElementById("cartTotal");
  const countBadge = document.getElementById("cartCount");
  const checkoutBtn = document.getElementById("checkoutBtn");
  const okBox = document.getElementById("purchaseOk");
  const cartCycleTxt = document.getElementById("cartCycleTxt");

  
  const footerContainer = footer?.parentElement;
  let warnBox = document.getElementById("cartWarnBox");
  if (!warnBox && footerContainer) {
    warnBox = document.createElement("div");
    warnBox.id = "cartWarnBox";
    warnBox.className =
      "mx-6 mt-4 px-4 py-3 rounded-2xl bg-red-500/15 border border-red-400/20 text-red-200 hidden";
    warnBox.textContent = "Warning";
    footerContainer.insertBefore(warnBox, footerContainer.querySelector("#cartFooter"));
  }

  function showWarn(msg) {
    if (!warnBox) return;
    warnBox.textContent = msg || "Warning";
    warnBox.classList.remove("hidden");
  }
  function hideWarn() {
    if (!warnBox) return;
    warnBox.classList.add("hidden");
  }

  function show() {
    overlay.classList.remove("hidden");
    overlay.classList.add("flex");
  }
  function hide() {
    overlay.classList.add("hidden");
    overlay.classList.remove("flex");
    okBox && okBox.classList.add("hidden");
    hideWarn();
  }

  openCart &&
    openCart.addEventListener("click", async () => {
      await refresh();
      show();
    });

  closeCart && closeCart.addEventListener("click", hide);
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) hide();
  });

  async function api(action, data) {
    const fd = new FormData();
    fd.append("action", action);
    if (data) Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const r = await fetch("api_cart.php", { method: "POST", body: fd, cache: "no-store" });
    return r.json();
  }

  async function refresh() {
    const res = await fetch("api_cart.php?action=list", { cache: "no-store" });
    const json = await res.json();
    const items = json.items || [];
    itemsWrap.innerHTML = "";

    const total = items.reduce((s, it) => s + Number(it.price), 0);

    if (items.length === 0) {
      empty.classList.remove("hidden");
      footer.classList.add("hidden");
      countBadge.classList.add("hidden");
    } else {
      empty.classList.add("hidden");
      footer.classList.remove("hidden");
      totalEl.textContent = total;
      countBadge.classList.remove("hidden");
      countBadge.textContent = items.length;

     
      const cycle = items[0]?.billing_cycle === "yearly" ? "yearly" : "monthly";
      if (cartCycleTxt) cartCycleTxt.textContent = cycle === "yearly" ? "/year" : "/month";

      items.forEach((it) => {
        const div = document.createElement("div");
        div.className = "flex items-center justify-between p-4 bg-gray-50 rounded-lg";
        div.innerHTML = `
          <div>
            <h3 class="font-medium">${it.item_name} Membership</h3>
            <p class="text-sm text-gray-600">${cycle === "yearly" ? "Yearly subscription" : "Monthly subscription"}</p>
            <p class="font-bold mt-1">$${it.price}${cycle === "yearly" ? "/year" : "/month"}</p>
          </div>
          <button class="text-red-500 hover:text-red-700" data-remove="${it.item_key}">✕</button>
        `;
        itemsWrap.appendChild(div);
      });

      itemsWrap.querySelectorAll("[data-remove]").forEach((btn) => {
        btn.addEventListener("click", async () => {
          await api("remove", { item_key: btn.dataset.remove });
          await refresh();
        });
      });
    }
  }

  document.querySelectorAll(".addToCart").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const res = await api("add", {
        item_key: btn.dataset.itemKey,
        item_name: btn.dataset.itemName,
        price: btn.dataset.price,
        billing_cycle: btn.dataset.billingCycle || "monthly",
      });

      if (res && res.ok === false && res.reason === "HAS_ACTIVE_MEMBERSHIP") {
        window.showTopWarning?.("You already have an active membership. You can’t purchase another one.", "Active Membership");
        showWarn("You already have an active membership. You can’t purchase another one.");
        await refresh();
        show();
        return;
      }

      await refresh();
      show();
    });
  });

  checkoutBtn &&
    checkoutBtn.addEventListener("click", async () => {
      hideWarn();

      const res = await api("checkout");
      if (res && res.ok === false && res.reason === "HAS_ACTIVE_MEMBERSHIP") {
        window.showTopWarning?.("You already have an active membership. Purchase is blocked.", "Active Membership");
        showWarn("You already have an active membership. Purchase is blocked.");
        return;
      }

      checkoutBtn.disabled = true;
      okBox && okBox.classList.remove("hidden");

      setTimeout(async () => {
        await refresh();
        checkoutBtn.disabled = false;
      }, 1200);
    });

  await refresh();
})();


(async function () {
  const form = document.getElementById("bookingForm");
  if (!form) return;

  const list = document.getElementById("bookingList");
  const empty = document.getElementById("bookingEmpty");
  const ok = document.getElementById("bookingSuccess");

  async function refresh() {
    const res = await fetch("api_booking.php?action=list", { cache: "no-store" });
    const json = await res.json();
    const items = json.bookings || [];
    list.innerHTML = "";

    if (items.length === 0) {
      empty.classList.remove("hidden");
      return;
    }
    empty.classList.add("hidden");

    items.forEach((b) => {
      const card = document.createElement("div");
      card.className = "p-4 border border-gray-200 rounded-lg hover:border-orange-300 transition-colors";
      card.innerHTML = `
        <div class="flex items-start justify-between mb-2">
          <div>
            <h4 class="font-medium">${b.class_type}</h4>
            <p class="text-sm text-gray-600">with ${b.trainer_name}</p>
          </div>
          <span class="px-3 py-1 bg-orange-100 text-orange-700 text-sm rounded-full">${b.status}</span>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
          <div class="flex items-center gap-1"><span>📅</span><span>${b.date}</span></div>
          <div class="flex items-center gap-1"><span>⏱</span><span>${b.time_slot}</span></div>
        </div>
      `;
      list.appendChild(card);
    });
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    fd.append("action", "create");
    const res = await fetch("api_booking.php", { method: "POST", body: fd, cache: "no-store" });
    const json = await res.json();
    if (json.ok) {
      ok && ok.classList.remove("hidden");
      setTimeout(() => ok && ok.classList.add("hidden"), 2500);
      form.reset();
      await refresh();
    }
  });

  await refresh();
})();
