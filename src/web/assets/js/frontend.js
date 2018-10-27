Garnish.$doc.ready(function () {

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Tabs
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    new QarrTabs('#qarr-display-container');

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Report Abuse
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.qarr-entry-ra-btn').on('click', function (e) {
        e.preventDefault();
        var that = $(this);
        var elementId = $(this).data('element-id');
        var type = $(this).data('type');

        var data = {
            id: elementId,
            type: type
        };

        data[QARR.csrfTokenName] = QARR.csrfTokenValue;

        $.post(QARR.actionUrl + 'qarr/elements/report-abuse', data, function (response, textStatus, jqXHR) {
            if (response.success) {
                that.parent().html('<span>Reported</span>');
            }
        });
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Star Changer
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.qarr-star:not(.static)').on('click', function () {
        $('.qarr-star').removeClass('selected');
        $('.qarr-star').removeClass('active');

        var rating = $(this).data('star-count');
        $(this).addClass('selected');
        $(this).prevAll().addClass('active');

        $('#qarr-rating-input').val(rating);
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Pagination
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    var qarrPaginations = $('.qarr-pagination');
    [].forEach.call(qarrPaginations, function (el) {
        new QarrPagination(el);
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Questions
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.add-answer').on('click', function (e) {
        e.preventDefault();

        var payload = {
            target: $(this),
            questionId: $(this).data('id'),
            authorName: $(this).data('user-name'),
            authorId: $(this).data('user-id')
        };

        var answerHud = new QarrAnswerHud(payload);
    });
});