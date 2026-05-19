function showToast(message, type = 'success') {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;

    var toastId = 'toast-' + Date.now();
    var toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastElement = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastElement, { delay: 4500 });
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

function processUrlToast() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('login_required')) {
        showToast('Debes iniciar sesión para comprar productos', 'warning');
    }
    if (urlParams.has('toast')) {
        var toastType = 'success';
        var message = urlParams.get('toast');
        switch (message) {
            case 'compra_realizada':
                message = 'Compra finalizada con éxito. Puedes cancelar en los siguientes 30 segundos.';
                toastType = 'success';
                break;
            case 'compra_cancelada':
                message = 'La compra fue cancelada y el stock se ha restaurado.';
                toastType = 'success';
                break;
            case 'cancelacion_expirada':
                urlParams.delete('toast');
                history.replaceState(null, '', window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
                return;
            case 'error_stock':
                message = 'No hay suficiente stock para completar la acción.';
                toastType = 'danger';
                break;
            default:
                message = message.replace(/_/g, ' ');
                toastType = 'info';
        }
        showToast(message, toastType);
        urlParams.delete('toast');
        history.replaceState(null, '', window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
    }
}

function initDarkMode() {
    var body = document.body;
    var button = document.getElementById('darkModeToggle');
    if (!button) return;

    var savedMode = localStorage.getItem('farmaciaModo');
    if (savedMode === 'dark') {
        body.classList.add('dark-mode');
        button.innerHTML = '<i class="bi bi-sun-fill"></i>';
    } else {
        body.classList.remove('dark-mode');
        button.innerHTML = '<i class="bi bi-moon-stars-fill"></i>';
    }

    button.addEventListener('click', function () {
        body.classList.toggle('dark-mode');
        var isDark = body.classList.contains('dark-mode');
        localStorage.setItem('farmaciaModo', isDark ? 'dark' : 'light');
        button.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
    });
}

function initTableSearch(searchInputSelector, tableSelector, dateFromSelector, dateToSelector) {
    var searchInput = document.querySelector(searchInputSelector);
    var table = document.querySelector(tableSelector);
    var dateFrom = dateFromSelector ? document.querySelector(dateFromSelector) : null;
    var dateTo = dateToSelector ? document.querySelector(dateToSelector) : null;
    if (!searchInput || !table) return;

    function parseDate(value) {
        if (!value) return null;
        var parsed = new Date(value);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function matchesRange(rowDate, from, to) {
        if (!from && !to) return true;
        if (!rowDate) return false;
        if (from && rowDate < from) return false;
        if (to && rowDate > to) return false;
        return true;
    }

    function filterRows() {
        var filter = searchInput.value.trim().toLowerCase();
        var fromDate = dateFrom ? parseDate(dateFrom.value) : null;
        var toDate = dateTo ? parseDate(dateTo.value) : null;
        var rows = table.querySelectorAll('tbody tr');
        var visibleCount = 0;
        rows.forEach(function (row) {
            var text = row.textContent.trim().toLowerCase();
            var dateCell = row.querySelector('td[data-fecha]');
            var rowDate = null;
            if (dateCell) {
                rowDate = parseDate(dateCell.dataset.fecha);
            }
            var matchesText = !filter || text.indexOf(filter) !== -1;
            var matchesDate = matchesRange(rowDate, fromDate, toDate);
            var visible = matchesText && matchesDate;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });
        var badge = document.querySelector(tableSelector + '-count');
        if (badge) {
            badge.textContent = visibleCount;
        }
    }

    searchInput.addEventListener('input', filterRows);
    if (dateFrom) {
        dateFrom.addEventListener('change', filterRows);
    }
    if (dateTo) {
        dateTo.addEventListener('change', filterRows);
    }
}

function initSearchInputs() {
    document.querySelectorAll('input[data-search-table]').forEach(function (input) {
        var tableSelector = input.dataset.searchTable;
        if (!tableSelector) return;
        var dateFromSelector = input.dataset.dateFrom || null;
        var dateToSelector = input.dataset.dateTo || null;
        initTableSearch('#' + input.id, tableSelector, dateFromSelector, dateToSelector);
    });
}

function initAutoSearchForms() {
    document.querySelectorAll('[data-auto-search-target]').forEach(function (input) {
        var target = document.querySelector(input.dataset.autoSearchTarget);
        if (!target) return;
        input.addEventListener('input', function () {
            target.dispatchEvent(new Event('input'));
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initAutoSearchForms();
        initSearchInputs();
    });
} else {
    initAutoSearchForms();
    initSearchInputs();
}
