import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
// --- Flash message (auto dismiss + fade, robust) ---
(function () {
  const FADE_MS = 500; // harus sama dgn duration-500 di class Tailwind

  function dismiss(el) {
    if (!el || el.dataset.dismissing === "1") return;
    el.dataset.dismissing = "1";
    el.classList.add("opacity-0");
    setTimeout(() => {
      if (el && el.parentNode) el.parentNode.removeChild(el);
    }, FADE_MS);
  }

  function bindFlash(el) {
    if (!el || el.dataset.bound === "1") return;
    el.dataset.bound = "1";

    // tombol close (opsional)
    const closeBtn = el.querySelector(".flash-close");
    if (closeBtn) closeBtn.addEventListener("click", () => dismiss(el), { once: true });

    // auto-timeout
    const timeout = Number(el.getAttribute("data-timeout") || 3500);
    if (timeout > 0) {
      // Mulai timer sedikit setelah paint supaya pasti terlihat
      requestAnimationFrame(() => {
        const tid = setTimeout(() => dismiss(el), timeout);
        // simpan id timer (kalau perlu dibatalkan)
        el.dataset.tid = String(tid);
      });
    }
  }

  function bindAll() {
    document.querySelectorAll("[data-flash]").forEach(bindFlash);
  }

  // 1) Saat dokumen siap
  document.addEventListener("DOMContentLoaded", bindAll);

  // 2) Saat halaman di-restore dari bfcache (Chrome/Firefox)
  window.addEventListener("pageshow", (e) => {
    // e.persisted === true -> halaman diambil dari cache
    // Kita re-bind agar timer jalan lagi
    bindAll();
  });

  // 3) Tangkap elemen flash yang ditambahkan setelah load (jaga-jaga)
  const mo = new MutationObserver((muts) => {
    muts.forEach((m) => {
      m.addedNodes.forEach((n) => {
        if (!(n instanceof Element)) return;
        if (n.matches && n.matches("[data-flash]")) bindFlash(n);
        n.querySelectorAll?.("[data-flash]").forEach(bindFlash);
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();

