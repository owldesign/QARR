var QarrEmailCorrespondence, QarrEmailModal, QarrPrompt; // Replies

var FeedbackResponse = Garnish.Base.extend({
  $replyBtn: null,
  $replyDeleteBtn: null,
  $replyEditBtn: null,
  $container: null,
  $spinner: null,
  id: null,
  elementId: null,
  modal: null,
  init: function init(container) {
    console.log(container); // this.id = obj.id
    // this.elementId = obj.elementId
    // this.$replyBtn = Garnish.$doc.find('.feedback-reply')
    // this.$container = Garnish.$doc.find('.response')
    //
    // if (this.$replyBtn.is(':visible')) {
    //     this.addListener(this.$replyBtn, 'click', 'showReplyModal')
    // }
    //
    // if (this.id) {
    //     this.$replyEditBtn = this.$container.find('#edit-feedback-btn')
    //     this.$replyDeleteBtn = this.$container.find('#delete-feedback-btn')
    //
    //     this.addListener(this.$replyEditBtn, 'click', 'editReplyModal')
    //     this.addListener(this.$replyDeleteBtn, 'click', 'deleteReply')
    // }
  },
  showReplyModal: function showReplyModal() {
    if (this.modal) {
      delete this.modal;
      this.modal = new ReplyModal(this);
    } else {
      this.modal = new ReplyModal(this);
    }

    this.modal.on('save', $.proxy(this, 'updateReply'));
  },
  updateReply: function updateReply(payload) {
    var _this = this;

    this.$spinner = $('<div class="spinner hidden"/>').appendTo(this.$container);
    this.$spinner.removeClass('hidden');
    this.$container.addClass('faded');
    data = {
      id: this.id,
      reply: payload.reply,
      elementId: this.elementId
    };
    Craft.postActionRequest('qarr/replies/save', data, $.proxy(function (response, textStatus) {
      if (textStatus === 'success') {
        _this.updateFeedbackHtml(response);
      }
    }, this));
    this.modal.hide();
  },
  updateFeedbackHtml: function updateFeedbackHtml(data) {
    this.id = data.response.id;
    var that = this;

    if (data.success) {
      setTimeout($.proxy(function () {
        this.$container.html(data.template);
        Craft.cp.displayNotice(Craft.t('qarr', 'Reply added'));
        this.$spinner.addClass('hidden');
        this.$container.removeClass('faded');
        this.$replyEditBtn = this.$container.find('#edit-feedback-btn');
        this.$replyDeleteBtn = this.$container.find('#delete-feedback-btn');
        this.addListener(this.$replyEditBtn, 'click', 'editReplyModal');
        this.addListener(this.$replyDeleteBtn, 'click', 'deleteReply'); // new QarrReplyToFeedback({
        //     id: data.response.id,
        //     elementId: data.response.elementId
        // })
        // this.$replyBtn.velocity({opacity: 0}, 500, function () {  })

        that.$replyBtn.addClass('opacity-50 cursor-not-allowed');
      }, this), 1000);
    }
  },
  editReplyModal: function editReplyModal(e) {
    e.preventDefault();
    console.log('editing reply');
  },
  deleteReply: function deleteReply(e) {
    var _this2 = this;

    e.preventDefault();
    console.log('deleting reply');
    var data = {
      id: this.id
    };
    Craft.postActionRequest('qarr/replies/delete', data, $.proxy(function (response, textStatus) {
      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Reply deleted'));

        _this2.$container.html('');

        _this2.$replyBtn.removeClass('opacity-50 cursor-not-allowed');
      }
    }, this));
  }
}); // let FeedbackResponse = Garnish.Base.extend({
//     $btn: null,
//     $responseContainer: null,
//     $replyTextarea: null,
//     $spinner: null,
//
//     modal: null,
//     reply: null,
//     placeholder: null,
//     type: null,
//     replyId: null,
//     elementId: null,
//     authorId: null,
//
//     init(el) {
//         this.$btn = $(el)
//         this.$responseContainer = $('.response')
//         this.$spinner = this.$responseContainer.find('.spinner')
//         this.type = this.$btn.data('type')
//         this.elementId = this.$btn.data('element-id')
//         this.authorId = this.$btn.data('author-id')
//         this.replyId = this.$btn.data('reply-id')
//         this.placeholder = this.$btn.data('placeholder')
//
//         this.addListener(this.$btn, 'click', 'openReplyModal')
//     },
//
//
//     openReplyModal(e) {
//         e.preventDefault()
//
//         if (this.modal) {
//             delete this.modal
//             this.modal = new QarrReplyModal(this)
//         } else {
//             this.modal = new QarrReplyModal(this)
//         }
//
//         this.modal.on('save', $.proxy(this, 'updateReply'))
//     },
//
//     updateReply(data) {
//         this.$spinner.removeClass('hidden');
//         this.$responseContainer.addClass('faded')
//
//         data = {
//             id: this.replyId,
//             reply: this.reply,
//             elementId: this.elementId,
//             authorId: this.authorId,
//         }
//
//         Craft.postActionRequest('qarr/replies/save', data, $.proxy(((response, textStatus) => {
//             if (textStatus === 'success') {
//                 $('.feedback-panel').addClass('has-response')
//                 this.updateFeedbackHtml(response.response)
//             }
//         }), this))
//
//         this.modal.hide()
//     },
//
//     updateFeedbackHtml(data) {
//         let that = this
//
//         Craft.postActionRequest('qarr/replies/get-markup', data, $.proxy(((response, textStatus) => {
//             if (response.success) {
//                 setTimeout($.proxy(function () {
//                     this.$responseContainer.html(response.template);
//                     Craft.cp.displayNotice(Craft.t('qarr', 'Reply added'))
//                     this.$spinner.addClass('hidden')
//                     this.$responseContainer.removeClass('faded')
//                     new QarrReplyToFeedback('#reply-to-feedback-btn')
//
//                     this.$btn.velocity({opacity: 0}, 500, function () {
//                         that.$btn.hide()
//                     })
//
//                 }, this), 1000);
//             }
//         }), this))
//     }
// })

var ReplyModal = Garnish.Modal.extend({
  $container: null,
  $form: null,
  $errorContainer: null,
  type: null,
  parent: null,
  init: function init(parent, type) {
    this.$container = Garnish.$bod.find('.response');
    this.type = type;
    var body, self;
    self = this;
    this.base();
    this.parent = parent;
    this.$form = $('<form class="modal fitted qarr-modal prompt-modal">').appendTo(Garnish.$bod);
    this.setContainer(this.$form);
    body = $(['<div class="body">', '<header class="header">', '<span class="header-text tracking-wider text-lg font-normal text-gray-700">' + Craft.t('qarr', 'Replying to Feedback') + '</span>', '</header>', '<label class="block text-gray-700 text-sm font-medium mb-2">' + Craft.t('qarr', 'Reply message') + '</label>', '<textarea id="reply-text" class="outline-none rounded p-4 bg-gray-100 text-gray-700" rows="6" cols="70" placeholder="' + Craft.t('qarr', 'Leave a reply...') + '"></textarea>', '<div class="error-container relative">', '<span class="error-message absolute font-xs text-red-400 pt-2" data-error-message="' + Craft.t('qarr', 'Reply message cannot be blank.') + '"></span>', '</div>', '<div class="buttons">', '<button type="button" class="cancel bg-gray-100 hover:bg-gray-200 text-gray-600 py-2 px-4 rounded mr-2 ml-auto">' + Craft.t('qarr', 'Cancel') + '</button>', '<button type="submit" class="btn-modal submit bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded">' + Craft.t('qarr', 'Add reply') + '</button>', '</div>', '</div>'].join('')).appendTo(this.$form);
    this.show();
    this.$cancelBtn = body.find('.cancel');
    this.$replyTextarea = body.find('#reply-text');
    setTimeout($.proxy(function () {
      self.$replyTextarea.focus();
    }, this), 100);
    this.addListener(this.$cancelBtn, 'click', 'hide');
    this.addListener(this.$form, 'submit', 'save');
  },
  save: function save(e) {
    e.preventDefault();
    this.reply = this.$replyTextarea.val();
    this.$errorContainer = this.$form.find('.error-message');

    if (this.reply === '') {
      Garnish.shake(this.$container);

      if (this.$replyTextarea.val() === '') {
        this.$replyTextarea.addClass('border border-solid border-red-200');
        this.$errorContainer.html(this.$errorContainer.data('error-message'));
      }
    } else {
      if (this.type === 'new') {
        console.log('append to container');
        this.$container.html();
      } else {
        this.trigger('save', {
          reply: this.reply
        });
      }
    }
  }
}); // Emails

QarrEmailModal = Garnish.Modal.extend({
  widget: null,
  init: function init(widget) {
    var body, self;
    self = this;
    this.base();
    this.widget = widget;
    this.$form = $('<form class="modal fitted qarr-modal prompt-modal modal-blue">').appendTo(Garnish.$bod);
    this.setContainer(this.$form); // TODO: Make From Name dynamic to use site admin name
    // TODO: Make subject use entry title

    body = $(['<div class="body">', '<header class="header">', '<span class="header-text">' + Craft.t('qarr', 'Sending Email') + '</span>', '<span class="icon"><i class="fa fa-mail-bulk"></i></span>', '</header>', '<div class="qarr-field custom-field field-white-bg">', '<input class="text fullwidth" type="text" id="reply-subject" name="reply-subject" autocomplete="off">', '<label id="reply-subject-label" for="reply-subject" data-label="' + Craft.t('qarr', 'Subject') + '" data-error-message="' + Craft.t('qarr', 'Subject cannot be blank.') + '">' + Craft.t('qarr', 'Subject') + '</label>', '<span class="qarr-error-icon"><i class="fa fa-exclamation-triangle"></i></span>', '</div>', '<div class="qarr-field custom-field field-white-bg">', '<textarea id="reply-message" rows="6"></textarea>', '<label id="reply-message-label" for="reply-message" data-label="' + Craft.t('qarr', 'Message') + '" data-error-message="' + Craft.t('qarr', 'Message cannot be blank.') + '">' + Craft.t('qarr', 'Message') + '</label>', '<span class="qarr-error-icon"><i class="fa fa-exclamation-triangle"></i></span>', '</div>', '<div class="buttons">', '<input type="button" class="btn btn-modal cancel" value="' + Craft.t('qarr', 'Cancel') + '">', '<input type="submit" class="btn btn-modal submit" value="' + Craft.t('qarr', 'Send') + '">', '</div>', '</div>'].join('')).appendTo(this.$form);
    this.show();
    this.$cancelBtn = body.find('.cancel');
    this.$subjectInput = body.find('#reply-subject');
    this.$messageInput = body.find('#reply-message');
    new QarrInputField(this.$subjectInput.parent());
    new QarrTextareaField(this.$messageInput.parent());
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
    this.email = {
      subject: this.$subject,
      message: this.$message
    };
    this.$subjectInput.removeClass('has-error');
    this.$messageInput.removeClass('has-error');

    if (this.$subjectInput.val() === '' && this.$messageInput.val() === '') {
      // Garnish.shake(this.$container)
      if (this.$subjectInput.val() === '') {
        this.$subjectInput.parent().addClass('has-error');
        subjectLabel.html(subjectLabel.data('error-message'));
      }

      if (this.$messageInput.val() === '') {
        this.$messageInput.parent().addClass('has-error');
        messageLabel.html(messageLabel.data('error-message'));
      }
    } else {
      this.trigger('save', {
        email: this.email
      });
    }
  }
});
QarrEmailCorrespondence = Garnish.Base.extend({
  $btn: null,
  $btnText: null,
  $spinner: null,
  $waveLoaderContainer: null,
  $waveLoader: null,
  type: null,
  entryId: null,
  modal: null,
  wavifyOptions: {
    height: 20,
    bones: 3,
    amplitude: 40,
    color: '#B289EF',
    speed: .45
  },
  init: function init(el) {
    this.$btn = $(el);
    this.entryId = $(el).data('entry-id');
    this.type = $(el).data('type');
    this.$btnText = this.$btn.find('.link-text');
    this.$spinner = this.$btn.find('.spinner');
    this.$waveLoaderContainer = this.$btn.parent().parent().find('.loader-container');
    this.$waveLoader = this.$waveLoaderContainer.find('.wave-loader');
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

    var that = this;
    var email = data.email;
    data = {
      type: this.type,
      entryId: this.entryId,
      subject: email.subject,
      message: email.message
    };
    this.$btnText.addClass('hide');
    this.$spinner.removeClass('hidden');
    this.$waveLoader = $('.wave-loader').wavify(this.wavifyOptions);
    Craft.postActionRequest('qarr/correspondence/send-mail', data, $.proxy(function (response, textStatus) {
      if (textStatus === 'success') {
        _this3.$waveLoaderContainer.velocity({
          height: '150%'
        }, 2000, function () {
          that.$waveLoaderContainer.css({
            'height': '100%',
            'background-color': '#B289EF'
          });
          that.$waveLoader.kill();
        });

        _this3.emailSent();
      }
    }, this));
    this.modal.hide();
  },
  emailSent: function emailSent() {
    var that = this;
    this.$waveLoaderContainer.velocity({
      opacity: 0
    }, 'fast', function () {
      that.$waveLoaderContainer.css({
        'pointer-events': 'none'
      });
      that.$btn.parent().html('<span class="mail-result">' + Craft.t('qarr', 'Mail sent!') + '</span>');
    });
  }
}); // Prompts

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
    this.$promptMessage = $('<p class="prompt-msg"/>').appendTo(this.$prompt);
    this.$promptChoices = $('<div class="options"></div>').appendTo(this.$prompt);
    this.$promptButtons = $('<div class="buttons right"/>').appendTo(this.$prompt);
    this.modal.setContainer(this.$modalContainerDiv);
    this.$promptMessage.html('<div class="font-medium">' + message + '</div>');
    var $cancelButton = $('<button class="cancel bg-gray-100 hover:bg-gray-200 text-gray-600 py-2 px-4 rounded mr-2 ml-auto">' + Craft.t('qarr', 'Cancel') + '</button>').appendTo(this.$promptButtons),
        $submitBtn = $('<button type="submit" class="submit bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded">' + Craft.t('qarr', 'OK') + '</button>').appendTo(this.$promptButtons);

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
