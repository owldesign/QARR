// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Feedback Reply
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
var FeedbackResponse = Garnish.Base.extend({
  $replyBtn: null,
  $replyDeleteBtn: null,
  $replyEditBtn: null,
  $container: null,
  $spinner: null,
  id: null,
  elementId: null,
  reply: null,
  modal: null,
  init: function init(container) {
    this.$container = container;
    var obj = this.$container.find('.response-container');
    this.id = obj.data('id');
    this.elementId = obj.data('element-id');
    this.reply = obj.data('reply');
    this.$replyBtn = this.$container.find('#reply-to-feedback');
    this.$replyEditBtn = this.$container.find('#edit-feedback-btn');
    this.$replyDeleteBtn = this.$container.find('#delete-feedback-btn');
    this.addListener(this.$replyEditBtn, 'click', 'handleEditReply');
    this.addListener(this.$replyDeleteBtn, 'click', 'handleDeleteReply');
  },
  handleEditReply: function handleEditReply() {
    if (this.modal) {
      delete this.modal;
      this.modal = new ReplyModal(this, 'edit', this.elementId);
    } else {
      this.modal = new ReplyModal(this, 'edit', this.elementId);
    }
  },
  handleDeleteReply: function handleDeleteReply(e) {
    var _this = this;

    e.preventDefault();
    var data = {
      id: this.id
    };
    Craft.postActionRequest('qarr/replies/delete', data, $.proxy(function (response, textStatus) {
      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Reply deleted'));

        _this.$container.html('');

        $('#reply-to-feedback').removeClass('disabled');

        _this.$container.parent().parent().removeClass('has-response');
      }
    }, this));
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Reply Modal
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var ReplyModal = Garnish.Modal.extend({
  $parentContainer: null,
  $form: null,
  $modalContainer: null,
  $errorContainer: null,
  $spinner: null,
  isValidate: false,
  type: null,
  elementId: null,
  parent: null,
  init: function init(parent, type, elementId) {
    this.base();
    this.$parentContainer = Garnish.$bod.find('.response');
    this.type = type;
    this.parent = parent;
    this.elementId = elementId;

    if (parent) {
      this.id = parent.id;
    }

    this.$form = $('<form class="modal fitted qarr-modal prompt-modal">').appendTo(Garnish.$bod);
    this.setContainer(this.$form);
    var btnText = Craft.t('qarr', 'Add reply');

    if (this.type === 'edit') {
      btnText = Craft.t('qarr', 'Update reply');
    }

    this.$modalContainer = $("\n                <div class=\"header\">\n                    <h1>".concat(Craft.t('qarr', 'Replying to feedback'), "</h1>\n                </div>\n                \n                <div class=\"body\">\n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\">").concat(Craft.t('qarr', 'Reply message'), "</label>\n                        </div>\n                        <div class=\"input\">\n                            <textarea id=\"reply-text\" class=\"text fullwidth template ltr\" rows=\"6\" cols=\"70\" placeholder=\"").concat(Craft.t('qarr', 'Leave a reply...'), "\"></textarea>\n                        </div>\n                    </div>\n                </div>\n                \n                <div class=\"footer\">\n                    <div class=\"buttons right last\">\n                        <input type=\"button\" class=\"btn cancel\" value=\"").concat(Craft.t('qarr', 'Cancel'), "\">\n                        <input type=\"submit\" class=\"btn submit\" value=\"").concat(btnText, "\">\n                        <span class=\"spinner hidden\"></span>\n                    </div>\n                </div>\n        "));
    this.$modalContainer.appendTo(this.$form);
    this.show();
    this.$cancelBtn = this.$modalContainer.find('.cancel');
    this.$replyTextarea = this.$modalContainer.find('#reply-text');
    this.$errorContainer = this.$modalContainer.find('.error-message');
    this.$spinner = this.$modalContainer.find('.spinner');

    if (this.type === 'edit') {
      this.$replyTextarea.val(this.parent.reply);
    }

    setTimeout($.proxy(function () {
      this.$replyTextarea.focus();
    }, this), 100);
    this.addListener(this.$cancelBtn, 'click', 'handleCancel');
    this.addListener(this.$form, 'submit', 'handleOk');
  },
  handleOk: function handleOk(e) {
    var _this2 = this;

    e.preventDefault();
    this.validateForm();

    if (this.isValidate) {
      var data = {
        id: this.id,
        reply: this.reply,
        elementId: this.elementId
      };
      Craft.postActionRequest('qarr/replies/save', data, $.proxy(function (response, textStatus) {
        if (textStatus === 'success') {
          if (_this2.type === 'new') {
            Craft.cp.displayNotice(Craft.t('qarr', 'Reply added'));
          } else {
            Craft.cp.displayNotice(Craft.t('qarr', 'Reply updated'));
          }

          _this2.$parentContainer.html(response.template);

          new FeedbackResponse(_this2.$parentContainer);
          $('#reply-to-feedback').addClass('disabled');

          _this2.$parentContainer.parent().parent().addClass('has-response');

          _this2.handleSuccess();
        }
      }, this));
    }
  },
  validateForm: function validateForm() {
    this.reply = this.$replyTextarea.val();
    this.$spinner.removeClass('hidden');

    if (this.reply === '') {
      Garnish.shake(this.$container);
      this.$spinner.addClass('hidden');
      this.$replyTextarea.addClass('error');
      this.$errorContainer.html(this.$errorContainer.data('error-message'));
      this.isValidate = false;
    } else {
      this.$spinner.addClass('hidden');
      this.$replyTextarea.removeClass('error');
      this.$errorContainer.html('');
      this.isValidate = true;
    }
  },
  handleSuccess: function handleSuccess() {
    this.hide();
  },
  handleCancel: function handleCancel() {
    this.hide();
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Email Correspondence
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var QarrEmailCorrespondence = Garnish.Base.extend({
  $btn: null,
  $btnText: null,
  $loader: null,
  type: null,
  entryId: null,
  modal: null,
  init: function init(el) {
    this.$btn = $(el);
    this.entryId = $(el).data('element-id');
    this.type = $(el).data('type');
    this.$btnText = this.$btn.find('.link-text');
    this.$loader = this.$btn.find('.loader');
    this.addListener(this.$btn, 'click', 'openEmailModal');
  },
  openEmailModal: function openEmailModal(e) {
    e.preventDefault();

    if (this.modal) {
      delete this.modal;
      this.modal = new QarrEmailModal(this);
    } else {
      this.modal = new QarrEmailModal(this);
    }

    this.modal.on('save', $.proxy(this, 'sendEmail'));
  },
  sendEmail: function sendEmail(data) {
    var _this3 = this;

    var email = data.email;
    data = {
      type: this.type,
      entryId: this.entryId,
      subject: email.subject,
      message: email.message
    };
    this.$btnText.addClass('hide');
    this.$loader.removeClass('hidden');
    Craft.postActionRequest('qarr/correspondence/send-mail', data, $.proxy(function (response, textStatus) {
      console.log(response);

      if (textStatus === 'success') {
        _this3.emailSent(response.entry);
      }
    }, this));
    this.modal.hide();
  },
  emailSent: function emailSent(entry) {
    var $container = this.$btn.parent().parent().find('.block-body');
    var $html = $(['<div class="block-field mb-4">', '<div class="text-xs opacity-50 mb-2">' + Craft.t('qarr', 'Sent now') + '</div>', '<div class="mb-2"><span class="font-semibold">' + Craft.t('qarr', 'Subject') + '</span> <p class="m-0">' + entry.subject + '</p></div>', '<div class="mb-2"><span class="font-semibold">' + Craft.t('qarr', 'Message') + '</span> <p class="m-0">' + entry.message + '</p></div>', '</div>'].join(''));
    $container.append($html);
    Craft.cp.displayNotice(Craft.t('qarr', 'Mail sent'));
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Emails
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var QarrEmailModal = Garnish.Modal.extend({
  $body: null,
  isValid: false,
  init: function init() {
    var self = this;
    this.base();
    this.$form = $('<form class="modal fitted qarr-modal prompt-modal modal-blue">').appendTo(Garnish.$bod);
    this.setContainer(this.$form); // TODO: Make From Name dynamic to use site admin name
    // TODO: Make subject use entry title

    this.$body = $("\n                <div class=\"header\">\n                    <h1>".concat(Craft.t('qarr', 'Sending Email'), "</h1>\n                </div>\n                \n                <div class=\"body\">\n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\" for=\"reply-subject\">").concat(Craft.t('qarr', 'Subject'), "</label>\n                        </div>\n                        <div class=\"input\">\n                            <input class=\"text fullwidth\" type=\"text\" id=\"reply-subject\" name=\"reply-subject\" autocomplete=\"off\">\n                        </div>\n                    </div>\n                    \n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\">").concat(Craft.t('qarr', 'Message'), "</label>\n                        </div>\n                        <div class=\"input\">\n                            <textarea id=\"reply-message\" class=\"text fullwidth\" rows=\"6\" cols=\"70\" placeholder=\"").concat(Craft.t('qarr', 'Email body message...'), "\"></textarea>\n                        </div>\n                    </div>\n                    \n                    <ul class=\"qarr-errors\"></ul>\n                </div>\n                \n                <div class=\"footer\">\n                    <div class=\"buttons right last\">\n                        <input type=\"button\" class=\"btn cancel\" value=\"").concat(Craft.t('qarr', 'Cancel'), "\">\n                        <input type=\"submit\" class=\"btn submit\" value=\"").concat(Craft.t('qarr', 'Send'), "\">\n                        <span class=\"spinner hidden\"></span>\n                    </div>\n                </div>\n        "));
    this.$body.appendTo(this.$form);
    this.show();
    this.$cancelBtn = this.$body.find('.cancel');
    this.$subjectInput = this.$body.find('#reply-subject');
    this.$messageInput = this.$body.find('#reply-message');
    setTimeout($.proxy(function () {
      self.$subjectInput.focus();
    }, this), 100);
    this.addListener(this.$cancelBtn, 'click', 'hide');
    this.addListener(this.$form, 'submit', 'save');
  },
  save: function save(e) {
    var that = this;
    e.preventDefault();
    var $errorsContainer = this.$form.find('.qarr-errors');
    this.email = {
      subject: this.$subjectInput.val(),
      message: this.$messageInput.val()
    };
    $errorsContainer.html('');
    this.$subjectInput.removeClass('error');
    this.$messageInput.removeClass('error');
    $.each(this.email, function (key, value) {
      if (value === '') {
        that.isValid = false;
      }
    });

    if (this.$subjectInput.val() === '' || this.$messageInput.val() === '') {
      Garnish.shake(this.$container);

      if (this.$subjectInput.val() === '') {
        this.$subjectInput.addClass('error');
        $errorsContainer.append('<li>' + Craft.t('qarr', 'Subject is required') + '</li>');
      }

      if (this.$messageInput.val() === '') {
        this.$messageInput.addClass('error');
        $errorsContainer.append('<li>' + Craft.t('qarr', 'Message is required') + '</li>');
      }
    } else {
      this.trigger('save', {
        email: this.email
      });
    }

    this.updateSizeAndPosition();
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Prompts
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var QarrPrompt = Garnish.Base.extend({
  modal: null,
  $modalContainerDiv: null,
  $prompt: null,
  $promptChoices: null,
  init: function init(message, choices) {
    this.showPrompt(message, choices, $.proxy(this, '_handleSelection'));
  },
  showPrompt: function showPrompt(message, choices, callback) {
    this._promptCallback = callback;

    if (this.modal === null) {
      this.modal = new Garnish.Modal({
        closeOtherModals: false
      });
    }

    if (this.$modalContainerDiv === null) {
      this.$modalContainerDiv = $('<div class="modal fitted prompt-modal"></div>').addClass().appendTo(Garnish.$bod);
    }

    this.$prompt = $('<div class="body"></div>').appendTo(this.$modalContainerDiv.empty());
    this.$footer = $("<div class=\"footer\"></div>").appendTo(this.$modalContainerDiv);
    this.$promptMessage = $('<p class="prompt-msg"/>').appendTo(this.$prompt);
    this.$promptChoices = $('<div class="options"></div>').appendTo(this.$prompt);
    this.$promptButtons = $('<div class="buttons right"/>').appendTo(this.$footer);
    this.modal.setContainer(this.$modalContainerDiv);
    this.$promptMessage.html('<div class="font-medium">' + message + '</div>');
    var $cancelButton = $("<input type=\"button\" class=\"btn cancel\" value=\"".concat(Craft.t('qarr', 'Cancel'), "\">")).appendTo(this.$promptButtons),
        $submitBtn = $("<input type=\"submit\" class=\"btn submit\" value=\"".concat(Craft.t('qarr', 'OK'), "\">")).appendTo(this.$promptButtons);

    if (choices) {
      $submitBtn.addClass('disabled');

      for (var i = 0; i < choices.length; i++) {
        var $radioButtonHtml = $('<div><label><input type="radio" name="promptAction" value="' + choices[i].value + '"/> ' + choices[i].title + '</label></div>').appendTo(this.$promptChoices),
            $radioButton = $radioButtonHtml.find('input');
        this.addListener($radioButton, 'click', function () {
          $submitBtn.removeClass('disabled');
        });
      }
    }

    this.addListener($submitBtn, 'click', function (ev) {
      ev.preventDefault();

      if (this.choices) {
        var choice = $(ev.currentTarget).parents('.modal').find('input[name=promptAction]:checked').val();

        this._selectPromptChoice(choice);
      } else {
        this._selectPromptChoice('ok');
      }
    });
    this.addListener($cancelButton, 'click', function () {
      var choice = 'cancel';

      this._selectPromptChoice(choice);
    });
    this.modal.show();
    this.modal.removeListener(Garnish.Modal.$shade, 'click');
    this.addListener(Garnish.Modal.$shade, 'click', '_cancelPrompt');
  },
  _handleSelection: function _handleSelection(response) {
    this.trigger('response', {
      response: response
    });
  },
  _selectPromptChoice: function _selectPromptChoice(choice) {
    this.$prompt.fadeOut('fast', $.proxy(function () {
      this.modal.hide();

      this._promptCallback(choice);
    }, this));
  },
  _cancelPrompt: function _cancelPrompt() {
    this._selectPromptChoice('cancel', true);
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Answers Instance
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var Answers = {};
Answers.Container = Garnish.Base.extend({
  $container: null,
  $items: null,
  $loader: null,
  payload: null,
  init: function init(el) {
    parent = this;
    this.$container = $(el);
    this.$loader = this.$container.find('.loader');
    this.$items = this.$container.find('.answer-item');

    if (this.$items.length > 0) {
      $.each(this.$items, function (i, item) {
        new Answers.Answer(item, parent);
      });
    }
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Answer Item
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

Answers.Answer = Garnish.Base.extend({
  $item: null,
  $actionBtn: null,
  $statusLabel: null,
  id: null,
  questionId: null,
  parent: null,
  payload: null,
  init: function init(el, parent) {
    this.parent = parent;
    this.$item = $(el);
    this.$actionBtn = this.$item.find('.action-btn');
    this.$statusLabel = this.$item.find('.answer-status');
    this.payload = {
      id: this.$item.data('id'),
      questionId: this.$item.data('parent-id')
    };
    this.addListener(this.$actionBtn, 'click', 'performAction');
  },
  performAction: function performAction(e) {
    var _this4 = this;

    var that = this;
    var action = e.target.dataset.actionType;

    if (action === 'delete') {
      var $form = $('<section class="hud-wrapper">' + '<label for="">' + Craft.t("qarr", "Are you sure?") + '</label>' + '</section>' + '<section class="hud-footer qarr-footer mb-4">' + '<button type="button" class="qarr-btn btn-small cancel">' + Craft.t("qarr", "Cancel") + '</button>' + '<button type="submit" class="qarr-btn btn-purple btn-small submit">' + Craft.t("qarr", "Delete") + '</button>' + '</section>').appendTo(Garnish.$bod);
      this.hud = new Garnish.HUD(e.target, $form, {
        hudClass: 'hud qarr-hud',
        bodyClass: 'body',
        closeOtherHUDs: false
      });
      var $cancelBtn = this.hud.$footer.find('.cancel');
      this.addListener($cancelBtn, 'click', function () {
        this.hud.hide();
      });
      this.hud.on('submit', function (e) {
        that.deleteElement();
        that.hud.hide();
      });
    }

    if (action === 'status') {
      this.parent.$loader.removeClass('hidden');
      this.payload.status = e.target.dataset.status;
      Craft.postActionRequest('qarr/answers/update-status', this.payload, $.proxy(function (response, textStatus) {
        if (response.success) {
          Craft.cp.displayNotice(Craft.t('qarr', 'Answer status updated'));

          _this4.$item.addClass('status-changed');

          _this4.updateAnswer();
        }
      }, this));
    }
  },
  updateAnswer: function updateAnswer() {
    if (this.payload.status === 'approved') {
      this.$statusLabel.removeClass('rejected');
      this.$statusLabel.addClass('approved');
      this.$item.removeClass('rejected');
      this.$item.addClass('approved');
    } else {
      this.$statusLabel.removeClass('approved');
      this.$statusLabel.addClass('rejected');
      this.$item.removeClass('approved');
      this.$item.addClass('rejected');
    }

    this.$statusLabel.html(this.payload.status);
    this.parent.$loader.addClass('hidden');
  },
  deleteElement: function deleteElement() {
    var _this5 = this;

    this.parent.$loader.removeClass('hidden');
    var newPayload = {
      id: this.payload.id
    };
    Craft.postActionRequest('qarr/answers/delete', newPayload, $.proxy(function (response, textStatus) {
      _this5.parent.$loader.addClass('hidden');

      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Answer deleted'));

        _this5.$item.addClass('item-deleted');

        _this5.$item.velocity('slideUp', {
          duration: 300
        });
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
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Element Selects
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

var ElementSelect = {};
ElementSelect.ElementSelectInput = Craft.BaseElementSelectInput.extend({
  onModalSelect: function onModalSelect(elements) {
    if (this.settings.limit) {
      var slotsLeft = this.settings.limit - this.$elements.length;

      if (elements.length > slotsLeft) {
        elements = elements.slice(0, slotsLeft);
      }
    }

    this.selectElements(elements);
    this.updateDisabledElementsInModal();
  },
  onSelectElements: function onSelectElements(elements) {
    this.trigger('selectElements', {
      elements: elements
    });
    this.settings.onSelectElements(elements);
    QARR.directLinkInstance.handleAddElement(elements[0]);
  },
  onRemoveElements: function onRemoveElements() {
    this.trigger('removeElements');
    this.settings.onRemoveElements();
    QARR.directLinkInstance.trigger('elementRemoved', this);
  }
});
