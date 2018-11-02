Garnish.$doc.ready(function () {

    if ($('#reply-email-btn').length > 0) {
        var emailCorrespondence = new QarrEmailCorrespondence('#reply-email-btn');
    }

    if ($('#reply-to-feedback-btn').length > 0) {
        var replyGo = new QarrReplyToFeedback('#reply-to-feedback-btn');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Clear Entry Abuse
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.abuse-reported-meta .btns').on('click', function (e) {
        e.preventDefault();

        var data = {
            id: $(this).data('entry-id'),
            type: $(this).data('type')
        };

        Craft.postActionRequest('qarr/elements/clear-abuse', data, $.proxy(function (response, textStatus) {
            if (response.success) {
                Craft.cp.displayNotice(Craft.t('qarr', 'Abuse cleared'));
                $('.abuse-reported-meta').velocity({ opacity: 0 }, 500, function () {
                    $('.abuse-reported-meta').remove();
                });
            }
        }, this));
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Update Entry Status
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.update-status-btn').on('click', function (e) {
        e.preventDefault();

        var data = {
            id: $(this).data('element-id'),
            status: $(this).data('status'),
            type: $(this).data('type')
        };

        Craft.postActionRequest('qarr/elements/update-status', data, $.proxy(function (response, textStatus) {
            if (response.success) {
                Craft.cp.displayNotice(Craft.t('qarr', 'Status updated'));
                if (response.entry.status === 'approved') {
                    $('.status-badge').removeClass('status-pending');
                    $('.status-badge').removeClass('status-rejected');
                }

                if (response.entry.status === 'rejected') {
                    $('.status-badge').removeClass('status-pending');
                    $('.status-badge').removeClass('status-approved');
                }
                $('.status-badge').addClass('status-' + response.entry.status);
                $('.status-badge').find('span').html(response.entry.status);
            }
        }, this));
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Delete Feedback
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // TODO: move this into a class in correspondence.js
    $('.feedback-panel #delete-feedback-btn').on('click', function (e) {
        e.preventDefault();

        var data = {
            id: $(this).data('reply-id')
        };

        Craft.postActionRequest('qarr/replies/delete', data, $.proxy(function (response, textStatus) {
            if (response.success) {
                Craft.cp.displayNotice(Craft.t('qarr', 'Reply deleted'));
                $('.panel-response').remove();
                $('.feedback-panel').removeClass('has-response');
                $('#reply-to-feedback-btn').show();
            }
        }, this));
    });

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Delete Entry
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.delete-entry').on('click', function (e) {
        e.preventDefault();

        var data = {
            id: $(this).data('element-id'),
            type: $(this).data('type')
        };

        var message = Craft.t('qarr', 'Deleting this review will also remove all its responses and correspondence?');
        var deletePrompt = new QarrPrompt(message, null);

        deletePrompt.on('response', function (response) {
            var _this = this;

            if (response.response === 'ok') {
                Craft.postActionRequest('qarr/reviews/delete', data, $.proxy(function (response, textStatus) {
                    if (response.success) {
                        setTimeout($.proxy(function () {
                            Craft.cp.displayNotice(Craft.t('qarr', 'Entry deleted, redirecting...'));
                            setTimeout($.proxy(function () {
                                Craft.redirectTo(Craft.getCpUrl() + '/qarr/reviews');
                            }, this), 1000);
                        }, _this), 1000);
                    }
                }, this));
            }
        });
    });
});
