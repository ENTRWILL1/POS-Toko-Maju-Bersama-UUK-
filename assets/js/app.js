document.addEventListener('submit', function (event) {
    const form = event.target;
    if (form.matches('[data-confirm]')) {
        const message = form.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
        if (!window.confirm(message)) event.preventDefault();
    }
});

function addTransactionRow() {
    const template = document.getElementById('transaction-row-template');
    const container = document.getElementById('transaction-items');
    if (!template || !container) return;
    container.appendChild(template.content.cloneNode(true));
}
