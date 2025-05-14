// Copyright Zikula, licensed MIT.

document.addEventListener('DOMContentLoaded', function () {
    const policyLinks = document.querySelectorAll('.policy-link');
    const modalTitle = document.querySelector('#policyModalTitle');
    const modalBody = document.querySelector('#policyModalBody');
    const modalElem = document.querySelector('#policyModal');
    const modal = new bootstrap.Modal('#policyModal', {});

    policyLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            modalTitle.textContent = this.textContent;
            const href = this.getAttribute('href') + '?raw=1';
            fetch(href)
                .then(function (response) {
                    return response.text();
                })
                .then(function (data) {
                    modalBody.innerHTML = data;
                    modal.show();
                })
                .catch(function (error) {
                    console.error('Error:', error);
                });
        });
    });

    modalElem.addEventListener('hidden.bs.modal', event => {
        modalBody.innerHTML = '<i class="fas fa-spin fa-cog fa-2x"></i>';
    });
});
