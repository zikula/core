// Copyright Zikula, licensed MIT.

document.addEventListener('DOMContentLoaded', function() {
    function toggleBodyFields() {
        var messageType = document.getElementById('zikulathemebundle_mailtest_messageType').value;
        var bodyHtmlRow = document.getElementById('zikulathemebundle_mailtest_bodyHtml_row');
        var bodyTextRow = document.getElementById('zikulathemebundle_mailtest_bodyText_row');

        var messageTypes = ['html', 'multipart'];
        if (-1 !== messageTypes.indexOf(messageType)) {
            bodyHtmlRow.classList.remove('d-none');
        } else {
            bodyHtmlRow.classList.add('d-none');
        }

        messageTypes = ['text', 'multipart'];
        if (-1 !== messageTypes.indexOf(messageType)) {
            bodyTextRow.classList.remove('d-none');
        } else {
            bodyTextRow.classList.add('d-none');
        }
    }

    var messageTypeInput = document.getElementById('zikulathemebundle_mailtest_messageType');
    messageTypeInput.addEventListener('change', toggleBodyFields);
    toggleBodyFields();
});
