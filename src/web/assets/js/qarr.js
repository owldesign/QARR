Garnish.$doc.ready(function () {
    // let wave1 = $('#feel-the-wave').wavify({
    //     height: 20,
    //     bones: 3,
    //     amplitude: 40,
    //     color: '#E9EFF4',
    //     speed: .257
    // });
    //
    // let wave2 = $('#feel-the-wave-two').wavify({
    //     height: 20,
    //     bones: 3,
    //     amplitude: 40,
    //     color: '#f8f9fd',
    //     speed: .25
    // });

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

        Craft.elementIndex.on('selectionChange', function (e) {
            if (e.target.elementType === 'owldesign\\qarr\\elements\\Review' || e.target.elementType === 'owldesign\\qarr\\elements\\Question') {
                var count = Craft.elementIndex.view.elementSelect.$selectedItems.length;

                var $countContainer = $('<div class="elements-selected-count">');
                var $countHtml = $('<span>' + count + ' ' + Craft.t("qarr", "items selected") + '</span>');

                if ($('.toolbar .flex').find('.elements-selected-count').length > 0) {
                    $('.toolbar .flex').find('.elements-selected-count').html($countHtml);
                } else {
                    $('.toolbar .flex').append($countContainer);
                    $countContainer.html($countHtml);
                }

                if (count === 0) {
                    $('.elements-selected-count').remove();
                }
            }
        });

        Craft.elementIndex.on('updateElements', function (e) {
            if (e.target.elementType === 'owldesign\\qarr\\elements\\Review' || e.target.elementType === 'owldesign\\qarr\\elements\\Question') {
                var count = Craft.elementIndex.view.elementSelect.$selectedItems.length;
                if (count === 0) {
                    $('.elements-selected-count').remove();
                }

                Craft.postActionRequest('qarr/elements/check-pending', {}, $.proxy(function (response, textStatus) {
                    if (response.success) {
                        window.qarrnav.handleResponse(response);

                        if (e.target.elementType === 'owldesign\\qarr\\elements\\Review') {
                            $('.count-badge.pending-reviews').html(response.reviews.pending);
                            $('.count-badge.approved-reviews').html(response.reviews.approved);
                            $('.count-badge.rejected-reviews').html(response.reviews.rejected);
                        }

                        if (e.target.elementType === 'owldesign\\qarr\\elements\\Question') {
                            $('.count-badge.pending-questions').html(response.questions.pending);
                            $('.count-badge.approved-questions').html(response.questions.approved);
                            $('.count-badge.rejected-questions').html(response.questions.rejected);
                        }
                    }
                }, this));
            }
        });
    }
});
