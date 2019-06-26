Garnish.$doc.ready(function () {
  // Feedback Reply
  // new FeedbackResponse();
  $('#reply-to-feedback').on('click', function (e) {
    var elementId = $(this).data('element-id');
    new ReplyModal(null, 'new', elementId);
  }); // if ($('#reply-to-feedback-btn').length > 0) {
  //     new QarrReplyToFeedback('#reply-to-feedback-btn');
  // }
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Update Entry Status
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  var $statusContainer = $('.element-status');
  $('.update-status-btn').on('click', function (e) {
    e.preventDefault();
    var data = {
      id: $(this).data('element-id'),
      status: $(this).data('status'),
      type: $(this).data('type')
    };
    Craft.postActionRequest('qarr/elements/update-status', data, $.proxy(function (response, textStatus) {
      if (response && response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Status updated'));

        if (response.entry.status === 'approved') {
          $statusContainer.removeClass('pending');
          $statusContainer.removeClass('rejected');
        }

        if (response.entry.status === 'rejected') {
          $statusContainer.removeClass('pending');
          $statusContainer.removeClass('approved');
        }

        $statusContainer.addClass(response.entry.status);
        $statusContainer.find('span').html(response.entry.status);
      }
    }, this));
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Delete Feedback
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // TODO: move this into a class in correspondence.js
  // $('#delete-feedback-btn').on('click', function(e) {
  //     e.preventDefault()
  //
  //     let data = {
  //         id: $(this).data('reply-id')
  //     };
  //
  //     Craft.postActionRequest('qarr/replies/delete', data, $.proxy(((response, textStatus) => {
  //         if (response.success) {
  //             Craft.cp.displayNotice(Craft.t('qarr', 'Reply deleted'));
  //             $('.panel-response').remove();
  //             $('.feedback-panel').removeClass('has-response');
  //             $('#reply-to-feedback-btn').show();
  //         }
  //     }), this))
  // });
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

    if (data.type === 'questions') {
      message = Craft.t('qarr', 'Deleting this question will also remove all its answers and correspondence?');
    }

    var deletePrompt = new QarrPrompt(message, null);
    deletePrompt.on('response', function (response) {
      var _this = this;

      if (response && response.response === 'ok') {
        Craft.postActionRequest('qarr/elements/delete', data, $.proxy(function (response, textStatus) {
          if (response.success) {
            setTimeout($.proxy(function () {
              Craft.cp.displayNotice(Craft.t('qarr', 'Entry deleted, redirecting...'));
              setTimeout($.proxy(function () {
                Craft.redirectTo(Craft.getCpUrl() + '/qarr/' + data.type);
              }, this), 1000);
            }, _this), 1000);
          }
        }, this));
      }
    });
  });
});
