document.addEventListener("DOMContentLoaded", () => {
    // ==== EXISTING TABS HOVER (punya kamu) ====
    const tabs = document.querySelectorAll(".dm-tab");
    const activeClasses =
        "dm-tab px-4 py-2 rounded-full text-sm font-medium bg-[#B9257F] text-white";
    const inactiveClasses =
        "dm-tab px-4 py-2 rounded-full text-sm font-medium bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]";

    tabs.forEach((tab) => {
        tab.addEventListener("mouseover", () => {
            if (!tab.classList.contains("text-white"))
                tab.classList.add("bg-[#F5F5F5]");
        });
        tab.addEventListener("mouseout", () => {
            if (!tab.classList.contains("text-white"))
                tab.className = inactiveClasses;
        });
    });
    tabs.forEach((tab) =>
        tab.addEventListener("click", (e) => {
            e.currentTarget.className = activeClasses;
        }),
    );

    // ==== 1) Toast Password Baru ====
    const pwToast = document.getElementById("pwToast");
    if (pwToast) {
        const closeBtn = document.getElementById("pwToastClose");
        const timeoutMs = parseInt(pwToast.dataset.timeout || "3000", 10);
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
});
