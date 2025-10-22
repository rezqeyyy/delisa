document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById("openRoleModal");
    const modal = document.getElementById("roleModal");
    const closeBtn = document.getElementById("closeRoleModal");
    const backdrop = document.getElementById("roleModalBackdrop");

    openBtn?.addEventListener("click", () => modal.classList.remove("hidden"));
    closeBtn?.addEventListener("click", () => modal.classList.add("hidden"));
    backdrop?.addEventListener("click", () => modal.classList.add("hidden"));
});
