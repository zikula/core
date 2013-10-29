jQuery(document).ready(function() {
    var $emails = $('#acme_addressbook_contact_emails');
    var $newEmailLinkLi = $('<li><a href="#" class="add_email_link btn"><i class="icon-plus"></i> Add email</a></li>');

    var $phoneNumbers = $('#acme_addressbook_contact_phoneNumbers');
    var $newPhoneNumberLinkLi = $('<li><a href="#" class="add_phone_number_link btn"><i class="icon-plus"></i> Add phone number</a></li>');

    initCollection($emails, $newEmailLinkLi);
    initCollection($phoneNumbers, $newPhoneNumberLinkLi);
});

function initCollection($collection, $newLinkLi) {
    // add a "delete" link to each existing entry
    $collection.find('li').each(function (index, li) {
        addDeleteLink($(li));
    });

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collection.data('index', $collection.find('li').length);

    // add the "add a tag" anchor and li to the tags ul
    $collection.append($newLinkLi);

    $newLinkLi.find('> a').on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addLi($collection, $newLinkLi);
    });
}

function addLi($collection, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collection.data('prototype');

    // get the new index
    var index = $collection.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var $newLi = $(prototype.replace(/__name__/g, index));

    // increase the index with one for the next item
    $collection.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    addDeleteLink($newLi);

    $newLinkLi.before($newLi);
}

function addDeleteLink($li) {
    // create a new "delete" link
    var $deleteLink = $('<a href="#"><i class="icon-minus-sign"></i></a>');

    // add link to the email form
    $li.find('input:last').after($deleteLink);

    $deleteLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove row from the form
        $(e.currentTarget).parents('li').fadeOut(300, function () {
            $(this).remove();
        });
    });
}
