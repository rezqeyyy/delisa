document.addEventListener("DOMContentLoaded", () => {
    // ==== 0) TABS HOVER (sudah ada) ====
    const tabs = document.querySelectorAll(".dm-tab");
    const activeClasses =
        "dm-tab px-4 py-2 rounded-full text-sm font-medium bg-[#B9257F] text-white";
    const inactiveClasses =
        "dm-tab px-4 py-2 rounded-full text-sm font-medium bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]";

    tabs.forEach((tab) => {
        tab.addEventListener("mouseover", () => {
            if (!tab.classList.contains("text-white")) {
                tab.classList.add("bg-[#F5F5F5]");
            }
        });
        tab.addEventListener("mouseout", () => {
            if (!tab.classList.contains("text-white")) {
                tab.className = inactiveClasses;
            }
        });
    });

    tabs.forEach((tab) =>
        tab.addEventListener("click", (e) => {
            e.currentTarget.className = activeClasses;
        }),
    );

    // ==== 1) Toast Password Baru (index) ====
    const pwToast = document.getElementById("pwToast");
    if (pwToast) {
        const closeBtn = document.getElementById("pwToastClose");
        const timeoutMs = parseInt(pwToast.dataset.timeout || "3000", 10);

        // --- simpan password ke localStorage (per akun) ---
        const userId = pwToast.dataset.userId;
        const pw = pwToast.dataset.password;
        if (userId && pw) {
            try {
                localStorage.setItem(`dm_pw_${userId}`, pw);
            } catch (e) {
                console.error("Gagal menyimpan password ke localStorage", e);
            }
        }

        const hide = () => {
            // animasi hilang
            pwToast.style.opacity = "0";
            pwToast.style.transform = "translateY(-6px)";
            setTimeout(() => pwToast.remove(), 250);
        };

        // auto hide
        setTimeout(hide, timeoutMs);

        // manual close
        if (closeBtn) closeBtn.addEventListener("click", hide);

        // Copy password
        const copyBtn = document.getElementById("pwCopyBtn");
        const pwValue = document.getElementById("pwValue");
        if (copyBtn && pwValue) {
            copyBtn.addEventListener("click", async () => {
                try {
                    await navigator.clipboard.writeText(
                        pwValue.textContent.trim(),
                    );
                    copyBtn.textContent = "Disalin";
                    setTimeout(() => (copyBtn.textContent = "Salin"), 1500);
                } catch {
                    // fallback select-all
                    const range = document.createRange();
                    range.selectNodeContents(pwValue);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
        }
    }

    // ==== 2) Konfirmasi submit tanpa inline script ====
    document.querySelectorAll("form[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (e) => {
            const msg = form.getAttribute("data-confirm") || "Yakin?";
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ==== 3) Data Master SHOW: tampilkan password dari localStorage ====
    const section = document.getElementById("dmPasswordSection");
    if (section) {
        const userId = section.dataset.userId;
        if (!userId) return;

        const key = `dm_pw_${userId}`;

        const valueWrapper = document.getElementById("dmPasswordValueWrapper");
        const valueEl = document.getElementById("dmPasswordValue");
        const infoEl = document.getElementById("dmPasswordInfo");
        const emptyInfoEl = document.getElementById("dmPasswordEmptyInfo");

        const showPassword = (pw, infoText) => {
            if (!valueWrapper || !valueEl) return;
            valueWrapper.classList.remove("hidden");
            valueEl.textContent = pw;

            if (emptyInfoEl) {
                emptyInfoEl.classList.add("hidden");
            }
            if (infoEl && infoText) {
                infoEl.textContent = infoText;
            }
        };

        // 3a) Jika baru saja reset & controller mengirim session('new_password')
        const initPw = section.dataset.initPassword;
        if (initPw) {
            try {
                localStorage.setItem(key, initPw);
            } catch (e) {
                console.error(
                    "Gagal menyimpan password ke localStorage dari show",
                    e,
                );
            }

            showPassword(
                initPw,
                "Password ini baru saja direset secara otomatis oleh sistem. Simpan untuk diberikan kepada petugas terkait.",
            );
            return;
        }

        // 3b) Kalau tidak ada initPw, cek apakah ada password tersimpan di localStorage
        try {
            const stored = localStorage.getItem(key);
            if (stored) {
                showPassword(
                    stored,
                    "Password ini adalah password acak terakhir hasil reset otomatis yang tersimpan di browser ini.",
                );
                return;
            }
        } catch (e) {
            console.error("Gagal membaca password dari localStorage", e);
        }

        // 3c) Kalau sampai sini, berarti belum pernah ada reset otomatis
        //     Biarkan dmPasswordEmptyInfo tampil apa adanya.
    }
});
