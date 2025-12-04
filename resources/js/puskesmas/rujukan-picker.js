import Swal from 'sweetalert2';

(function () {
    const btn = document.querySelector("#btnAjukanRujukan");
    if (!btn) return;

    const submitUrl = btn.dataset?.submitUrl || "";
    const searchUrl = btn.dataset?.searchUrl || "";
    const csrfToken = btn.dataset?.csrf || "";

    const modal = document.createElement("div");
    modal.id = "rujukanModal";
    modal.className =
        "fixed inset-0 z-[100] hidden items-center justify-center bg-black/40";
    modal.innerHTML = `
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-[#1D1D1D]">Ajukan Rujukan</h3>
          <button type="button" id="closeRujukanModal" class="text-[#B9257F] hover:underline">Tutup</button>
        </div>
        
        <!-- TAMBAHKAN TEXTAREA UNTUK CATATAN RUJUKAN -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Catatan Rujukan (opsional)</label>
          <textarea id="catatanRujukan" placeholder="Opsional: jelaskan alasan rujukan dan kondisi pasien..." 
                    class="w-full border border-[#D9D9D9] rounded-xl p-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#B9257F] focus:border-[#B9257F]" 
                    rows="3"></textarea>
        </div>

        <p class="text-sm text-[#6B7280] mb-3">Pilih Rumah Sakit tujuan rujukan:</p>
        <div class="mt-3">
          <input id="rsSearchInput" type="text" placeholder="Ketik untuk mencari rumah sakit..."
                 class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]">
        </div>
        <div id="rsList" class="mt-3 max-h-64 overflow-y-auto border border-[#D9D9D9] rounded-xl"></div>
        
        <div class="mt-4 flex justify-end">
          <!-- UBAH METHOD MENJADI AJAX & TAMBAH INPUT HIDDEN UNTUK CATATAN -->
          <form id="rujukForm">
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="rs_id" id="rsIdInput" value="">
            <button type="button" id="submitRujukanBtn"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-gray-400 text-white disabled:opacity-60 disabled:cursor-not-allowed" disabled>
              Kirim Permintaan Rujukan
            </button>
          </form>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    const btnClose = modal.querySelector("#closeRujukanModal");
    const input = modal.querySelector("#rsSearchInput");
    const list = modal.querySelector("#rsList");
    const btnSubmit = modal.querySelector("#submitRujukanBtn");
    const rsIdInput = modal.querySelector("#rsIdInput");
    const catatanTextarea = modal.querySelector("#catatanRujukan");

    const showPopup = (opts) => {
        // Pakai SweetAlert2 dari import; fallback ke alert biasa kalau error
        try {
            if (Swal && typeof Swal.fire === "function") {
                return Swal.fire(opts);
            }
        } catch (e) {
            // ignore
        }
        alert(String(opts?.text || ""));
        return Promise.resolve();
    };

    function setSubmitEnabled(enabled) {
        btnSubmit.classList.remove(
            "bg-gray-400",
            "hover:bg-gray-500",
            "bg-[#B9257F]",
            "hover:bg-[#a31f70]",
        );
        if (enabled) {
            btnSubmit.classList.add("bg-[#B9257F]", "hover:bg-[#a31f70]");
            btnSubmit.disabled = false;
        } else {
            btnSubmit.classList.add("bg-gray-400");
            btnSubmit.disabled = true;
        }
    }

    function validateForm() {
        const hasRsSelected = Boolean(rsIdInput.value);
        return hasRsSelected;
    }

    function openModal() {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        input.value = "";
        rsIdInput.value = "";
        catatanTextarea.value = "";
        setSubmitEnabled(false);
        loadOptions("");
        setTimeout(() => input.focus(), 50);
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        rsIdInput.value = "";
        catatanTextarea.value = "";
        setSubmitEnabled(false);
        list.innerHTML = "";
    }

    async function loadOptions(q) {
        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set("q", q || "");
            const res = await fetch(url.toString(), {
                method: "GET",
                credentials: "include",
            });
            if (!res.ok) throw new Error("HTTP " + res.status);
            const data = await res.json();
            renderList(data);
        } catch (err) {
            console.error("Error loadOptions RS:", err); // 🔍 DEBUG JS
            list.innerHTML =
                '<div class="p-3 text-sm text-red-600">Gagal memuat daftar rumah sakit.</div>';
        }
    }

    function renderList(items) {
        if (!items?.length) {
            list.innerHTML =
                '<div class="p-3 text-sm text-[#6B7280]">Tidak ada hasil.</div>';
            return;
        }
        list.innerHTML = items
            .map((item) => {
                const title = item.nama || "-";
                const subtitle = [
                    "Lokasi:",
                    item.alamat || "-",
                    "Kec:",
                    item.kecamatan || "-",
                    "Kel:",
                    item.kelurahan || "-",
                ].join(" ");
                return `
              <button type="button" data-id="${item.id}"
                      class="w-full text-left px-4 py-2 hover:bg-[#F9E5F1] border-b last:border-b-0">
                  <div class="font-medium text-[#1D1D1D]">${title}</div>
                  <div class="text-xs text-[#6B7280]">${subtitle}</div>
              </button>
          `;
            })
            .join("");

        list.querySelectorAll("button[data-id]").forEach((it) => {
            it.addEventListener("click", () => {
                list.querySelectorAll("button[data-id]").forEach((el) => {
                    el.classList.remove(
                        "bg-[#F9E5F1]",
                        "border",
                        "border-[#B9257F]/40",
                    );
                });
                it.classList.add(
                    "bg-[#F9E5F1]",
                    "border",
                    "border-[#B9257F]/40",
                );
                rsIdInput.value = it.getAttribute("data-id") || "";
                setSubmitEnabled(validateForm());
            });
        });
    }

    async function handleSubmit() {
        if (!validateForm()) {
            showPopup({
                icon: "warning",
                title: "Data belum lengkap",
                text: "Pilih rumah sakit terlebih dahulu.",
                confirmButtonColor: "#B9257F",
            });
            input.focus();
            return;
        }

        const submitData = {
            rs_id: rsIdInput.value,
            catatan_rujukan: catatanTextarea.value.trim() || null,
            _token: csrfToken,
        };

        try {
            btnSubmit.disabled = true;
            btnSubmit.textContent = "Mengirim...";

            const response = await fetch(submitUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(submitData),
            });

            const result = await response.json();

            if (result.success) {
                showPopup({
                    icon: "success",
                    title: "Berhasil!",
                    text: "Rujukan berhasil diajukan",
                    confirmButtonColor: "#B9257F",
                    confirmButtonText: "OK",
                }).then(() => {
                    closeModal();
                    location.reload();
                });
            } else {
                showPopup({
                    icon: "error",
                    title: "Gagal",
                    text: result.message || "Gagal mengajukan rujukan",
                    confirmButtonColor: "#B9257F",
                });
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Kirim Permintaan Rujukan";
            }
        } catch (error) {
            showPopup({
                icon: "error",
                title: "Error",
                text: "Terjadi kesalahan: " + error.message,
                confirmButtonColor: "#B9257F",
            });
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Kirim Permintaan Rujukan";
        }
    }

    // EVENT LISTENERS
    btn.addEventListener("click", (e) => {
        e.preventDefault();
        if (btn.disabled) return;
        openModal();
    });

    btnClose.addEventListener("click", closeModal);
    btnSubmit.addEventListener("click", handleSubmit);

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.classList.contains("hidden"))
            closeModal();
    });

    catatanTextarea.addEventListener("input", () => {
        setSubmitEnabled(validateForm());
    });

    let typingTimer = null;
    input.addEventListener("input", () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => loadOptions(input.value.trim()), 250);
    });
})();
