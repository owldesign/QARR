Garnish.$doc.ready(function () {
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Element Index
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  $('.customize-sources').on('mouseenter', function () {
    $('#sources').addClass('active');
  }).on('mouseleave', function () {
    $('#sources').removeClass('active');
  });
  $('.configure-elements').on('mouseenter', function () {
    $('.element-element').addClass('active');
  }).on('mouseleave', function () {
    $('.element-element').removeClass('active');
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Configure Elements
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.configure-elements').on('click', function (e) {
    e.preventDefault();
    new ConfigureElementsModal();
  });
  $('.elementindex').on('click', '.configure-element', function (e) {
    e.preventDefault();
    var target = $(this).data('target');
    var type = $(this).data('type');
    new ConfigureElementsModal(target, type);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
  } // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Answers Instances
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~


  if ($('#answers-element').length > 0) {
    new Answers.Container('#answers-element');
  } // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
        $statusContainer.removeClass('pulse');

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
