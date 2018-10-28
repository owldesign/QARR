var QarrEmailCorrespondence = void 0,
    QarrEmailModal = void 0,
    QarrReplyToFeedback = void 0,
    QarrReplyModal = void 0;

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
        var _this = this;

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
                _this.$waveLoaderContainer.velocity({ height: '150%' }, 2000, function () {
                    that.$waveLoaderContainer.css({ 'height': '100%', 'background-color': '#B289EF' });
                    that.$waveLoader.kill();
                });

                _this.emailSent();
            }
        }, this));

        this.modal.hide();
    },
    emailSent: function emailSent() {
        var that = this;

        this.$waveLoaderContainer.velocity({ opacity: 0 }, 'fast', function () {
            that.$waveLoaderContainer.css({ 'pointer-events': 'none' });
            that.$btn.parent().html('<span class="mail-result">' + Craft.t('qarr', 'Mail sent!') + '</span>');
        });
    }
});

QarrEmailModal = Garnish.Modal.extend({
    widget: null,

    init: function init(widget) {
        var body = void 0,
            self = void 0;
        self = this;
        this.base();

        this.widget = widget;

        this.$form = $('<form class="modal fitted qarr-modal prompt-modal modal-blue">').appendTo(Garnish.$bod);
        this.setContainer(this.$form);

        // TODO: Make From Name dynamic to use site admin name
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

QarrReplyToFeedback = Garnish.Base.extend({
    $btn: null,
    $responseContainer: null,
    $replyTextarea: null,
    $spinner: null,

    modal: null,
    reply: null,
    placeholder: null,
    type: null,
    replyId: null,
    elementId: null,
    authorId: null,

    init: function init(el) {
        this.$btn = $(el);
        this.$responseContainer = $('.panel-response');
        this.$spinner = $('.panel-response .spinner');
        this.type = this.$btn.data('type');
        this.elementId = this.$btn.data('element-id');
        this.authorId = this.$btn.data('author-id');
        this.replyId = this.$btn.data('reply-id');
        this.placeholder = this.$btn.data('placeholder');

        this.addListener(this.$btn, 'click', 'openReplyModal');
    },
    openReplyModal: function openReplyModal(e) {
        e.preventDefault();

        if (this.modal) {
            delete this.modal;
            this.modal = new QarrReplyModal(this);
        } else {
            this.modal = new QarrReplyModal(this);
        }

        this.modal.on('save', $.proxy(this, 'updateReply'));
    },
    updateReply: function updateReply(data) {
        var _this2 = this;

        this.$spinner.removeClass('hidden');
        this.$responseContainer.find('.response-container').addClass('faded');

        data = {
            id: this.replyId,
            reply: this.reply,
            elementId: this.elementId,
            authorId: this.authorId
        };

        Craft.postActionRequest('qarr/replies/save', data, $.proxy(function (response, textStatus) {
            if (textStatus === 'success') {
                $('.feedback-panel').addClass('has-response');
                _this2.updateFeedbackHtml(response.response);
            }
        }, this));

        this.modal.hide();
    },
    updateFeedbackHtml: function updateFeedbackHtml(data) {
        var _this3 = this;

        var that = this;

        Craft.postActionRequest('qarr/replies/get-markup', data, $.proxy(function (response, textStatus) {
            if (response.success) {
                setTimeout($.proxy(function () {
                    this.$responseContainer.html(response.template);
                    Craft.cp.displayNotice(Craft.t('qarr', 'Reply added'));
                    this.$spinner.addClass('hidden');
                    this.$responseContainer.find('.response-container').removeClass('faded');
                    new QarrReplyToFeedback('#reply-to-feedback-btn');
                    this.$btn.velocity({ opacity: 0 }, 500, function () {
                        that.$btn.remove();
                    });
                }, _this3), 1000);
            }
        }, this));
    }
});

QarrReplyModal = Garnish.Modal.extend({
    widget: null,

    init: function init(widget) {
        var body = void 0,
            self = void 0;
        self = this;
        this.base();
        this.widget = widget;

        this.$form = $('<form class="modal fitted qarr-modal prompt-modal">').appendTo(Garnish.$bod);
        this.setContainer(this.$form);

        body = $(['<div class="body">', '<header class="header">', '<span class="header-text">' + Craft.t('qarr', 'Reply to Feedback') + '</span>', '<span class="icon"><i class="fa fa-reply"></i></span>', '</header>', '<div class="qarr-field custom-field field-white-bg">', '<textarea id="reply-text" rows="6"></textarea>', '<label id="reply-text-label" for="reply-text" data-label="' + Craft.t('qarr', 'Reply Message') + '" data-error-message="' + Craft.t('qarr', 'Reply Message cannot be blank.') + '">' + Craft.t('qarr', 'Reply Message') + '</label>', '<span class="qarr-error-icon"><i class="fa fa-exclamation-triangle"></i></span>', '</div>', '<div class="buttons">', '<input type="button" class="btn btn-modal cancel" value="' + Craft.t('qarr', 'Cancel') + '">', '<input type="submit" class="btn btn-modal submit" value="' + Craft.t('qarr', 'Add') + '">', '</div>', '</div>'].join('')).appendTo(this.$form);

        this.show();
        this.$cancelBtn = body.find('.cancel');
        this.$replyTextarea = body.find('#reply-text');

        new QarrTextareaField(this.$replyTextarea.parent());

        if (this.widget.placeholder) {
            this.$replyTextarea.val(this.widget.placeholder);
        }

        setTimeout($.proxy(function () {
            self.$replyTextarea.focus();
        }, this), 100);

        this.addListener(this.$cancelBtn, 'click', 'hide');
        this.addListener(this.$form, 'submit', 'save');
    },
    save: function save(e) {
        e.preventDefault();
        this.reply = this.$replyTextarea.val();
        this.widget.reply = this.reply;

        var messageLabel = this.$replyTextarea.parent().find('label');

        if (this.reply === '') {
            // Garnish.shake(this.$container)

            if (this.$replyTextarea.val() === '') {
                this.$replyTextarea.parent().addClass('has-error');
                messageLabel.html(messageLabel.data('error-message'));
            }
        } else {
            this.trigger('save', {
                reply: this.reply
            });
        }
    }
});
