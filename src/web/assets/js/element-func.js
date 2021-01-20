/******/ (() => { // webpackBootstrap
/*!****************************************!*\
  !*** ./development/js/element-func.js ***!
  \****************************************/
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Feedback Reply
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
FeedbackResponse = Garnish.Base.extend({
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

ReplyModal = Garnish.Modal.extend({
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
// Email Correspondence Preview
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

QarrEmailCorrespondencePreview = Garnish.Base.extend({
  $btn: null,
  init: function init(el) {
    this.$btn = $(el);
    console.log(this.$btn);
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Email Correspondence
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

QarrEmailCorrespondence = Garnish.Base.extend({
  $btn: null,
  $btnText: null,
  $loader: null,
  type: null,
  entryId: null,
  modal: null,
  templates: null,
  init: function init(el) {
    this.$btn = $(el);
    this.entryId = $(el).data('element-id');
    this.type = $(el).data('type');
    this.$btnText = this.$btn.find('.link-text');
    this.$loader = this.$btn.find('.loader');
    this.getAvailableTemplates();
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
      message: email.message,
      templateId: email.templateId
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
  },
  getAvailableTemplates: function getAvailableTemplates() {
    var _this4 = this;

    Craft.postActionRequest('qarr/campaigns/email-templates/get-all-email-templates', $.proxy(function (response, textStatus) {
      if (response.success) {
        _this4.templates = response.options;
      }
    }, this));
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Emails
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

QarrEmailModal = Garnish.Modal.extend({
  $body: null,
  isValid: false,
  parent: null,
  init: function init(parent) {
    var self = this;
    this.base();
    this.parent = parent;
    this.$form = $('<form class="modal fitted qarr-modal prompt-modal modal-blue">').appendTo(Garnish.$bod);
    this.setContainer(this.$form); // TODO: Make From Name dynamic to use site admin name
    // TODO: Make subject use entry title

    var subjectPlaceholder = Craft.t('qarr', 'We received your {rating} star rating!');
    var subjectInstructions = Craft.t('qarr', 'Any submission data can be used here eg. {rating}');
    var messagePlaceholder = Craft.t('qarr', '{fullName} thank you for your {rating} star submission.');
    var messageInstruction = Craft.t('qarr', 'Markdown allowed. Any submission data can be used here eg. {fullName}');

    if (this.parent.type === 'questions') {
      subjectPlaceholder = Craft.t('qarr', 'We received your question about {element.title}!');
      subjectInstructions = Craft.t('qarr', 'Any submission data can be used here eg. {element.title}');
      messagePlaceholder = Craft.t('qarr', '{fullName} thank you for your question.');
    }

    this.$body = $("\n                <div class=\"header\">\n                    <h1>".concat(Craft.t('qarr', 'Sending Email'), "</h1>\n                </div>\n                \n                <div class=\"body\">\n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\" for=\"reply-template\">").concat(Craft.t('qarr', 'Email Template'), "</label>\n                            <div class=\"instructions\"><p>").concat(Craft.t('qarr', 'Select email template if you want to use custom template'), "</p></div>\n                        </div>\n                        <div class=\"input\">\n                            <div class=\"select fullwidth\">\n                                <select name=\"template\" id=\"reply-template\" class=\"text fullwidth\">\n                                    <option value=\"0\" selected>Default</option>\n                                    ").concat(Object.keys(this.parent.templates).map(function (key) {
      return '<option value="' + self.parent.templates[key].id + '">' + self.parent.templates[key].name + '</option>';
    }).join(''), "\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n                    \n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\" for=\"reply-subject\">").concat(Craft.t('qarr', 'Subject'), "</label>\n                            <div class=\"instructions\"><p>").concat(subjectInstructions, "</p></div>\n                        </div>\n                        <div class=\"input\">\n                            <input class=\"text fullwidth\" type=\"text\" id=\"reply-subject\" name=\"reply-subject\" autocomplete=\"off\" placeholder=\"").concat(subjectPlaceholder, "\">\n                        </div>\n                    </div>\n                    \n                    <div class=\"field\">\n                        <div class=\"heading\">\n                            <label class=\"qarr-label\">").concat(Craft.t('qarr', 'Message'), "</label>\n                            <div class=\"instructions\"><p>").concat(messageInstruction, "</p></div>\n                        </div>\n                        <div class=\"input\">\n                            <textarea id=\"reply-message\" class=\"text fullwidth\" rows=\"12\" cols=\"70\" placeholder=\"").concat(messagePlaceholder, "\"></textarea>\n                        </div>\n                    </div>\n                    \n                    <ul class=\"qarr-errors\"></ul>\n                </div>\n                \n                <div class=\"footer\">\n                    <div class=\"buttons right last\">\n                        <input type=\"button\" class=\"btn cancel\" value=\"").concat(Craft.t('qarr', 'Cancel'), "\">\n                        <input type=\"submit\" class=\"btn submit\" value=\"").concat(Craft.t('qarr', 'Send'), "\">\n                        <span class=\"spinner hidden\"></span>\n                    </div>\n                </div>\n        "));
    this.$body.appendTo(this.$form);
    this.show();
    this.$cancelBtn = this.$body.find('.cancel');
    this.$subjectInput = this.$body.find('#reply-subject');
    this.$messageInput = this.$body.find('#reply-message');
    this.$templateInput = this.$body.find('#reply-template');
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
      message: this.$messageInput.val(),
      templateId: this.$templateInput.val()
    };
    $errorsContainer.html('');
    this.$subjectInput.removeClass('error');
    this.$messageInput.removeClass('error');
    this.$templateInput.removeClass('error');
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

QarrPrompt = Garnish.Base.extend({
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

Answers = {};
Answers.Container = Garnish.Base.extend({
  $container: null,
  $items: null,
  $loader: null,
  $tab: null,
  payload: null,
  init: function init(el) {
    parent = this;
    this.$container = $(el);
    this.$loader = this.$container.find('.loader');
    this.$items = this.$container.find('.answer-item'); // TODO: add pending count to this.$tab
    // this.$tab = $('#tab-1')

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
    var _this5 = this;

    var that = this;
    var action = e.target.dataset.actionType;

    if (action === 'delete') {
      var $hudContents = $();
      var $form = $('<div/>');
      var $footer = $('<div class="hud-footer"/>').appendTo($form);
      var $body = $("\n                <div>".concat(Craft.t("qarr", "Are you sure?"), "</div>\n            ")).appendTo($form);
      var $buttonsContainer = $('<div class="buttons right"/>').appendTo($footer);
      var $cancelBtn = $('<div class="btn">' + Craft.t('qarr', 'Cancel') + '</div>').appendTo($buttonsContainer);
      var $okBtn = $('<input class="btn submit" type="submit" value="' + Craft.t('qarr', 'Delete') + '"/>').appendTo($buttonsContainer);
      var $spinner = $('<div class="spinner hidden"/>').appendTo($buttonsContainer);
      $hudContents = $hudContents.add($form);
      this.hud = new Garnish.HUD(e.target, $hudContents, {
        hudClass: 'hud',
        bodyClass: 'body',
        closeOtherHUDs: false
      });
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

          _this5.$item.addClass('status-changed');

          _this5.updateAnswer();
        }
      }, this));
    }
  },
  updateAnswer: function updateAnswer() {
    if (this.payload.status === 'approved') {
      this.$statusLabel.removeClass('rejected');
      this.$statusLabel.addClass('approved');
      this.$item.removeClass('pending');
      this.$item.removeClass('rejected');
      this.$item.addClass('approved');
    } else {
      this.$statusLabel.removeClass('approved');
      this.$statusLabel.addClass('rejected');
      this.$item.removeClass('pending');
      this.$item.removeClass('approved');
      this.$item.addClass('rejected');
    }

    this.$statusLabel.html(this.payload.status);
    this.parent.$loader.addClass('hidden');
  },
  deleteElement: function deleteElement() {
    var _this6 = this;

    this.parent.$loader.removeClass('hidden');
    var newPayload = {
      id: this.payload.id
    };
    Craft.postActionRequest('qarr/answers/delete', newPayload, $.proxy(function (response, textStatus) {
      _this6.parent.$loader.addClass('hidden');

      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Answer deleted'));

        _this6.$item.addClass('item-deleted');

        _this6.$item.velocity('slideUp', {
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
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Configure Elements Modal
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

ConfigureElementsModal = Garnish.Modal.extend({
  $form: null,
  $modalContainer: null,
  $errorContainer: null,
  $spinner: null,
  elementIndex: null,
  type: null,
  target: null,
  init: function init(target, type) {
    this.base();
    this.elementIndex = Craft.elementIndex;

    if (target) {
      this.target = target;
    }

    if (type) {
      this.type = type;
    }

    this.setSettings({
      resizable: true
    });
    this.getModalContent();
    this.$form = $('<form class="modal elementselectormodal">').appendTo(Garnish.$bod);
    this.setContainer(this.$form);
  },
  getModalContent: function getModalContent() {
    var _this7 = this;

    Craft.postActionRequest('qarr/settings/configuration/get-element-settings-modal', {}, $.proxy(function (response, textStatus) {
      if (response.success) {
        _this7.buildModal($(response.template));
      }
    }, this));
  },
  buildModal: function buildModal(modalContainer) {
    var that = this;
    this.$modalContainer = modalContainer;
    this.$modalContainer.appendTo(this.$form);

    if (this.type && this.target) {
      setTimeout(function (e) {
        $('#elementAssetHandleName-' + that.type + '-' + that.target).focus();
      }, 100);
    }

    this.show();
    this.$cancelBtn = this.$modalContainer.find('.cancel');
    this.$spinner = this.$modalContainer.find('.spinner');
    this.addListener(this.$cancelBtn, 'click', 'handleCancel');
    this.addListener(this.$form, 'submit', 'handleOk');
  },
  handleOk: function handleOk(e) {
    var _this8 = this;

    e.preventDefault();
    var data = this.$form.serialize();
    Craft.postActionRequest('qarr/settings/configuration/save-element-settings', data, $.proxy(function (response, textStatus) {
      if (response.success) {
        _this8.handleSuccess();

        _this8.elementIndex.updateElements();
      }
    }, this));
  },
  handleSuccess: function handleSuccess() {
    this.hide();
  },
  handleCancel: function handleCancel() {
    this.hide();
  }
}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Configure Elements HUD
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

ConfigureElementHud = Garnish.Base.extend({
  $form: null,
  type: null,
  target: null,
  elementIndex: null,
  init: function init(el) {
    // TODO: update this to open a hud and only save single item
    this.type = el.data('type');
    this.target = el.data('target'); // Temp

    new ConfigureElementsModal(this.target, this.type); // let that = this
    // this.elementIndex = Craft.elementIndex
    //
    // let $hudContents = $()
    // let randomId = Math.floor(Math.random() * 11)
    //
    // this.$form = $('<form/>')
    // let $footer = $('<div class="hud-footer"/>').appendTo(this.$form)
    // let $body = $(`
    //         <input type="hidden" name="pluginHandle" value="qarr">
    //         <div class="field">
    //             <div class="heading">
    //                 <label class="qarr-label" for="asset-handle-${randomId}">${Craft.t('qarr', 'Asset Handle')}</label>
    //             </div>
    //             <div class="input">
    //                 <input class="text fullwidth" type="text" id="asset-handle-${randomId}" name="settings[elementAssetHandleName][${this.target}]" autocomplete="off" placeholder="Asset handle">
    //             </div>
    //         </div>
    //     `).appendTo(this.$form)
    // let $buttonsContainer = $('<div class="buttons right"/>').appendTo($footer)
    // let $cancelBtn = $('<div class="btn">' + Craft.t('qarr', 'Cancel') + '</div>').appendTo($buttonsContainer)
    // let $okBtn = $('<input class="btn submit" type="submit" value="' + Craft.t('qarr', 'Save') + '"/>').appendTo($buttonsContainer)
    // let $spinner = $('<div class="spinner hidden"/>').appendTo($buttonsContainer)
    //
    // $hudContents = $hudContents.add(this.$form)
    //
    // this.hud = new Garnish.HUD(el, $hudContents, {
    //     hudClass: 'hud',
    //     bodyClass: 'body',
    //     closeOtherHUDs: false
    // })
    //
    // this.addListener($cancelBtn, 'click', function() {
    //     this.hud.hide()
    // })
    //
    // this.addListener(this.$form, 'submit', 'handleOk')
    //
    // this.hud.on('submit', function(e) {
    //     that.handleOk(e)
    //     that.hud.hide()
    // })
  } // handleOk() {
  //     let data = this.$form.serialize()
  //     Craft.postActionRequest('qarr/settings/configuration/save-element-settings', data, $.proxy(((response, textStatus) => {
  //         if (response.success) {
  //             this.elementIndex.updateElements()
  //         }
  //     }), this))
  // },

}); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Color Input
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

QarrColorInput = Garnish.Base.extend({
  $container: null,
  $input: null,
  $colorContainer: null,
  $colorPreview: null,
  $colorInput: null,
  init: function init(container) {
    this.$container = $(container);
    this.$input = this.$container.children('.color-input');
    this.$colorContainer = this.$container.children('.color');
    this.$colorPreview = this.$colorContainer.children('.color-preview');
    this.createColorInput();
    this.handleTextChange();
    this.addListener(this.$input, 'textchange', 'handleTextChange');
  },
  createColorInput: function createColorInput() {
    var input = document.createElement('input');
    input.setAttribute('type', 'color');

    if (input.type !== 'color') {
      // The browser doesn't support input[type=color]
      return;
    }

    this.$colorContainer.removeClass('static');
    this.$colorInput = $(input).addClass('hidden').insertAfter(this.$input);
    this.addListener(this.$colorContainer, 'click', function () {
      this.$colorInput.trigger('click');
    });
    this.addListener(this.$colorInput, 'change', 'updateColor');
  },
  updateColor: function updateColor() {
    this.$input.val(this.$colorInput.val());
    this.$input.data('garnish-textchange-value', this.$colorInput.val());
    this.handleTextChange();
  },
  handleTextChange: function handleTextChange() {
    var val = this.$input.val(); // If empty, set the preview to transparent

    if (!val.length || val === '#') {
      this.$colorPreview.css('background-color', '');
      return;
    } // Make sure the value starts with a #


    if (val[0] !== '#') {
      val = '#' + val;
      this.$input.val(val);
      this.$input.data('garnish-textchange-value', val);
    }

    this.$colorPreview.css('background-color', val);

    if (this.$colorInput) {
      this.$colorInput.val(val);
    }
  }
}, {
  _browserSupportsColorInputs: null,
  doesBrowserSupportColorInputs: function doesBrowserSupportColorInputs() {
    if (Craft.ColorInput._browserSupportsColorInputs === null) {}

    return Craft.ColorInput._browserSupportsColorInputs;
  }
});
/******/ })()
;