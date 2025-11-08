document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('skriningFilterForm');
    const select = document.getElementById('statusSelect');
    if (!form || !select) return;

    select.addEventListener('change', function () {
        form.submit();
    });
});