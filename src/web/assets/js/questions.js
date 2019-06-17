var AnswerItem;
AnswerItem = Garnish.Base.extend({
  $container: null,
  $approveBtn: null,
  $rejectBtn: null,
  $deleteBtn: null,
  $statusLabel: null,
  statusLabelText: null,
  id: null,
  questionId: null,
  init: function init(el) {
    this.$container = $(el);
    this.$approveBtn = this.$container.find('.btn-approve');
    this.$rejectBtn = this.$container.find('.btn-reject');
    this.$deleteBtn = this.$container.find('.btn-delete');
    this.$statusLabel = this.$container.find('.panel-item-status');
    this.statusLabelText = this.$statusLabel.find('span');
    this.id = this.$container.data('id');
    this.questionId = this.$container.data('parent-id');
    this.addListener(this.$approveBtn, 'click', 'performAction');
    this.addListener(this.$rejectBtn, 'click', 'performAction');
    this.addListener(this.$deleteBtn, 'click', 'performAction');
  },
  performAction: function performAction(e) {
    var _this = this;

    e.preventDefault();
    var action = $(e.currentTarget).data('action');
    var data = {
      id: this.id,
      action: action
    };
    Craft.postActionRequest('qarr/answers/perform-action', data, $.proxy(function (response, textStatus) {
      if (response.success) {
        if (response.status !== 'deleted') {
          _this.updateStatus(response);

          Craft.cp.displayNotice(Craft.t('qarr', 'Answer status changed'));
        } else {
          _this.$container.velocity('slideUp', {
            duration: 300
          });

          Craft.cp.displayNotice(Craft.t('qarr', 'Answer deleted'));
        }
      }
    }, this));
  },
  updateStatus: function updateStatus(response) {
    var that = this;
    var oldStatus = this.statusLabelText.text();
    this.statusLabelText.velocity('transition.fadeOut', {
      duration: 350,
      complete: function complete() {
        that.statusLabelText.html(response.status);
        that.$statusLabel.removeClass('status-' + oldStatus);
        that.$statusLabel.addClass('status-' + response.status);
        that.statusLabelText.velocity('transition.fadeIn', {
          duration: 100
        });
      }
    });
  }
});
Garnish.$doc.ready(function () {
  if ($('#reply-email-btn').length > 0) {
    var emailCorrespondence = new QarrEmailCorrespondence('#reply-email-btn');
  } // Tippy


  var tippies = tippy('.tippy', {
    theme: 'light'
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
        $('.abuse-reported-meta').velocity({
          opacity: 0
        }, 500, function () {
          $('.abuse-reported-meta').remove();
        });
      }
    }, this));
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Delete Entry
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.delete-entry').on('click', function (e) {
    e.preventDefault();
    var data = {
      id: $(this).data('element-id'),
      type: $(this).data('type')
    };
    var message = Craft.t('qarr', 'Deleting this question will also remove all its answers and correspondence?');
    var deletePrompt = new QarrPrompt(message, null);
    deletePrompt.on('response', function (response) {
      var _this2 = this;

      if (response.response === 'ok') {
        Craft.postActionRequest('qarr/questions/delete', data, $.proxy(function (response, textStatus) {
          if (response.success) {
            setTimeout($.proxy(function () {
              Craft.cp.displayNotice(Craft.t('qarr', 'Entry deleted, redirecting...'));
              setTimeout($.proxy(function () {
                Craft.redirectTo(Craft.getCpUrl() + '/qarr/reviews');
              }, this), 1000);
            }, _this2), 1000);
          }
        }, this));
      }
    });
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Answer Item
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $.each($('.answer-item'), function (i, el) {
    new AnswerItem(el);
  });
});
