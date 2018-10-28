Garnish.$doc.ready(function () {
    var wave1 = $('#feel-the-wave').wavify({
        height: 20,
        bones: 3,
        amplitude: 40,
        color: '#E9EFF4',
        speed: .257
    });

    var wave2 = $('#feel-the-wave-two').wavify({
        height: 20,
        bones: 3,
        amplitude: 40,
        color: '#f8f9fd',
        speed: .25
    });

    var qarrInputFields = document.querySelectorAll('.custom-field');
    [].forEach.call(qarrInputFields, function (el) {
        new QarrInputField(el);
    });

    $('.custom-select label').on('click', function (e) {
        var select = $(this).parent().find('select');
        select.select2('open');
    });

    $('.custom-select select').select2({
        minimumResultsForSearch: Infinity,
        width: '100%',
        placeholder: ""
    });

    // Handle Badge Counters Globally
    if (Craft.elementIndex) {
        Craft.elementIndex.on('updateElements', function (e) {
            if (e.target.elementType === 'owldesign\\qarr\\elements\\Review' || e.target.elementType === 'owldesign\\qarr\\elements\\Question') {
                Craft.postActionRequest('qarr/elements/check-pending', {}, $.proxy(function (response, textStatus) {
                    if (response.success) {
                        window.qarrnav.handleResponse(response);
                        $('.reviews-count-header').html(response.reviews);
                        $('.questions-count-header').html(response.questions);
                    }
                }, this));
            }
        });
    }
});
