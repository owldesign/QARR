/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!****************************************!*\
  !*** ./development/js/element-edit.js ***!
  \****************************************/
Garnish.$doc.ready(function () {
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Feedback Reply
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  $('#reply-to-feedback').on('click', function (e) {
    var elementId = $(this).data('element-id');
    new ReplyModal(null, 'new', elementId);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Email Correspondence
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  if ($('#reply-email-btn').length > 0) {
    new QarrEmailCorrespondence('#reply-email-btn');
  }

  $('.preview-email').on('click', function (e) {
    e.preventDefault();
    new QarrEmailCorrespondencePreview(e.target);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Answers Instances
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  if ($('#answers').length > 0) {
    new Answers.Container('#answers');
  } // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Update Entry Status
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~


  var $statusContainer = $('.element-status');
  var $statusWrapper = $statusContainer.find('.status-tag');
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
          $statusWrapper.removeClass('pending');
          $statusWrapper.removeClass('rejected');
        }

        if (response.entry.status === 'rejected') {
          $statusWrapper.removeClass('pending');
          $statusWrapper.removeClass('approved');
        }

        $statusWrapper.addClass(response.entry.status);
        $statusContainer.find('.status-text').html(response.entry.status);
      }
    }, this));
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
/******/ })()
;