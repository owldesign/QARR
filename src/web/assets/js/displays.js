Garnish.$doc.ready(function () {
    $('.tag-link').on('click', function (e) {
        var target = $($(this).data('target'));
        var field = $(this).data('field');

        target.val(target.val() + field);
        target.focus();
    });
});