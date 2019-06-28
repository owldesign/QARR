// Feedback Reply
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
    } // this.modal.on('updated', $.proxy(this, 'handleUpdatedReply'))

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

        $('#reply-to-feedback').removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
      }
    }, this));
  }
}); // Reply Modal

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

    this.$modalContainer = $(['<div class="body">', '<header class="header">', '<h2>' + Craft.t('qarr', 'Replying to Feedback') + '</h2>', '</header>', '<label class="qarr-label">' + Craft.t('qarr', 'Reply message') + '</label>', '<textarea id="reply-text" class="qarr-textarea" rows="6" cols="70" placeholder="' + Craft.t('qarr', 'Leave a reply...') + '"></textarea>', '<div class="error-container relative">', '<span class="error-message absolute font-xs text-red-400 pt-2" data-error-message="' + Craft.t('qarr', 'Reply message cannot be blank.') + '"></span>', '</div>', '<div class="buttons right">', '<span class="spinner hidden"></span>', '<button type="button" class="cancel qarr-btn mr-2">' + Craft.t('qarr', 'Cancel') + '</button>', '<button type="submit" class="btn-modal submit qarr-btn btn-purple">' + btnText + '</button>', '</div>', '</div>'].join('')).appendTo(this.$form);
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
          $('#reply-to-feedback').addClass('opacity-50 cursor-not-allowed').prop('disabled', true);

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
      this.$replyTextarea.addClass('border border-solid border-red-200');
      this.$errorContainer.html(this.$errorContainer.data('error-message'));
      this.isValidate = false;
    } else {
      this.$spinner.addClass('hidden');
      this.$replyTextarea.removeClass('border border-solid border-red-200');
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
}); // Email Correspondence

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
      if (textStatus === 'success') {
        _this3.emailSent();
      }
    }, this));
    this.modal.hide();
  },
  emailSent: function emailSent() {
    this.$btn.parent().html('<span class="mail-result">' + Craft.t('qarr', 'Mail sent!') + '</span>');
  }
}); // Emails

var QarrEmailModal = Garnish.Modal.extend({
  init: function init() {
    var body, self;
    self = this;
    this.base();
    this.$form = $('<form class="modal fitted qarr-modal prompt-modal modal-blue">').appendTo(Garnish.$bod);
    this.setContainer(this.$form); // TODO: Make From Name dynamic to use site admin name
    // TODO: Make subject use entry title

    body = $(['<div class="body">', '<header class="header">', '<h2>' + Craft.t('qarr', 'Sending Email') + '</h2>', '</header>', '<div class="qarr-field mb-4">', '<label id="reply-subject-label" class="qarr-label" for="reply-subject" data-label="' + Craft.t('qarr', 'Subject') + '" data-error-message="' + Craft.t('qarr', 'Subject cannot be blank.') + '">' + Craft.t('qarr', 'Subject') + '</label>', '<input class="qarr-input" type="text" id="reply-subject" name="reply-subject" autocomplete="off">', '</div>', '<div class="qarr-field">', '<label id="reply-message-label" class="qarr-label" for="reply-message" data-label="' + Craft.t('qarr', 'Message') + '" data-error-message="' + Craft.t('qarr', 'Message cannot be blank.') + '">' + Craft.t('qarr', 'Message') + '</label>', '<textarea id="reply-message" class="qarr-textarea" rows="6" cols="60"></textarea>', '</div>', '<ul class="qarr-errors"></ul>', '<div class="buttons right">', '<button type="button" class="qarr-btn cancel mr-2">' + Craft.t('qarr', 'Cancel') + '</button>', '<button type="submit" class="qarr-btn btn-purple submit">' + Craft.t('qarr', 'Send') + '</button>', '</div>', '</div>'].join('')).appendTo(this.$form);
    this.show();
    this.$cancelBtn = body.find('.cancel');
    this.$subjectInput = body.find('#reply-subject');
    this.$messageInput = body.find('#reply-message');
    setTimeout($.proxy(function () {
      self.$subjectInput.focus();
    }, this), 100);
    this.addListener(this.$cancelBtn, 'click', 'hide');
    this.addListener(this.$form, 'submit', 'save');
  },
  save: function save(e) {
    e.preventDefault();
    this.$subject = this.$subjectInput.val();
    this.$message = this.$messageInput.val();
    var subjectLabel = this.$subjectInput.parent().find('label');
    var messageLabel = this.$messageInput.parent().find('label');
    var $errorsContainer = this.$form.find('.qarr-errors');
    this.email = {
      subject: this.$subject,
      message: this.$message
    };
    $errorsContainer.html('');
    this.$subjectInput.removeClass('has-error');
    this.$messageInput.removeClass('has-error');

    if (this.$subjectInput.val() === '') {
      Garnish.shake(this.$container);
    }

    if (this.$messageInput.val() === '') {} // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS
    // TODO: FIXING THIS


    if (this.$subjectInput.val() === '' && this.$messageInput.val() === '') {// Garnish.shake(this.$container)
      //
      // if (this.$subjectInput.val() === '') {
      //     this.$subjectInput.addClass('has-error')
      //     $errorsContainer.append('<li>'+subjectLabel.data('error-message')+'</li>')
      // }
      //
      // if (this.$messageInput.val() === '') {
      //     this.$messageInput.addClass('has-error')
      //     $errorsContainer.append('<li>'+messageLabel.data('error-message')+'</li>')
      // }
      // this.updateSizeAndPosition()
    } else {// this.trigger('save', {
        //     email: this.email
        // })
      }
  }
}); // Prompts

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
    this.$promptMessage = $('<p class="prompt-msg"/>').appendTo(this.$prompt);
    this.$promptChoices = $('<div class="options"></div>').appendTo(this.$prompt);
    this.$promptButtons = $('<div class="buttons right"/>').appendTo(this.$prompt);
    this.modal.setContainer(this.$modalContainerDiv);
    this.$promptMessage.html('<div class="font-medium">' + message + '</div>');
    var $cancelButton = $('<button class="cancel qarr-btn  mr-2">' + Craft.t('qarr', 'Cancel') + '</button>').appendTo(this.$promptButtons),
        $submitBtn = $('<button type="submit" class="submit qarr-btn btn-purple">' + Craft.t('qarr', 'OK') + '</button>').appendTo(this.$promptButtons);

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

    this.addListener($submitBtn, 'activate', function (ev) {
      if (this.choices) {
        var choice = $(ev.currentTarget).parents('.modal').find('input[name=promptAction]:checked').val();

        this._selectPromptChoice(choice);
      } else {
        this._selectPromptChoice('ok');
      }
    });
    this.addListener($cancelButton, 'activate', function () {
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
});
