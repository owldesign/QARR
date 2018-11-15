Garnish.$doc.ready(function () {

    // Icons
    $('#rule-icon').on('keyup', function (e) {
        var value = $(this).val();
        var html = $(this).parent().find('.qarr-input-icon');
        var icon = window.FontAwesome.icon({ prefix: 'fal', iconName: value });

        if (icon) {
            console.log(icon.html);
            html.html(icon.html);
        }
    });

    // Data List
    if ($('#rule-data').length > 0) {
        var dataInput = document.getElementById('rule-data');
        var tagify = new Tagify(dataInput);

        tagify.DOM.input.classList.add('tagify__input--outside');
        tagify.DOM.scope.parentNode.insertBefore(tagify.DOM.input, tagify.DOM.scope);

        document.querySelector('.tags--removeAllBtn').addEventListener('click', function (e) {
            tagify.removeAllTags();
            $('.tags--removeAllBtn').addClass('btn-disabled');
        }.bind(tagify));

        tagify.on('add', function (e) {
            if (tagify.value.length > 0) {
                $('.tags--removeAllBtn').removeClass('btn-disabled');
            }
        });

        tagify.on('remove', function (e) {
            if (tagify.value.length === 0) {
                $('.tags--removeAllBtn').addClass('btn-disabled');
            }
        });
    }
});
