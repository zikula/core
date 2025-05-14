// Copyright Zikula, licensed MIT.

window.addEventListener('load', function () {
    document.querySelectorAll('#phpinfo table').forEach(el => el.classList.add('table', 'table-striped', 'table-bordered'));
}, false);
