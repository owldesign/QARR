

var QarrTabs = Garnish.Base.extend({
    $container: null,
    $tabLinksContainer: null,
    $tabLink: null,
    $tabContentContainer: null,
    $tabContent: null,

    targetLink: null,
    target: null,
    selectedTab: 'reviews',

    reviewsContainer: null,
    reviewsContent: null,
    reviewsDisplayId: null,

    questionsContainer: null,
    questionsContent: null,
    questionsDisplayId: null,

    init: function init(el) {
        this.$container = $(el);
        this.$tabLinksContainer = this.$container.find('.qarr-tab-links');
        this.$tabLink = this.$tabLinksContainer.find('.qarr-tab-link');
        this.$tabContentContainer = this.$container.find('.qarr-tab-container');
        this.$tabContent = this.$container.find('.qarr-tab-content');

        this.reviewsContainer = this.$tabContentContainer.find('[data-qarr-reviews]');
        this.questionsContainer = this.$tabContentContainer.find('[data-qarr-questions]');

        var reviewParams = {
            type: 'review',
            displayId: this.reviewsContainer.data('display-id'),
            productId: this.reviewsContainer.data('product-id')
        };

        var questionParams = {
            type: 'question',
            displayId: this.questionsContainer.data('display-id'),
            productId: this.questionsContainer.data('product-id')
        };

        this.reviewsContent = new QarrTabContent(this.reviewsContainer, this, 'review', reviewParams);
        this.questionsContent = new QarrTabContent(this.questionsContainer, this, 'question', questionParams);

        // Toggle tab content if local storage is set
        this.checkTabSelection();

        this.addListener(this.$tabLink, 'click', 'handleTabClick');
    },
    checkTabSelection: function checkTabSelection() {
        if (window.localStorage.getItem('qarr-tab')) {
            this.selectedTab = window.localStorage.getItem('qarr-tab');
        }

        this.updateTabSelection();
    },
    handleTabClick: function handleTabClick(e) {
        e.preventDefault();
        this.targetLink = $(e.currentTarget);
        this.target = this.targetLink.data('target');
        this.selectedTab = this.target;
        window.localStorage.setItem('qarr-tab', this.target);

        this.updateTabSelection(this.targetLink);
    },
    updateTabSelection: function updateTabSelection(cta) {

        this.$tabLink.removeClass('active');

        if (this.selectedTab === 'reviews') {
            this.showReviews();
        } else {
            this.showQuestions();
        }
    },
    showReviews: function showReviews() {
        $('.qarr-tab-link-reviews').addClass('active');
        this.reviewsContainer.addClass('active');
        this.questionsContainer.removeClass('active');
    },
    showQuestions: function showQuestions() {
        $('.qarr-tab-link-questions').addClass('active');
        this.questionsContainer.addClass('active');
        this.reviewsContainer.removeClass('active');
    }
});

var QarrTabContent = Garnish.Base.extend({
    $container: null,
    $btn: null,

    type: null,
    displayId: null,
    productId: null,
    modal: null,
    tabContext: null,

    init: function init(el, context, type, params) {
        this.tabContext = context;
        this.type = type;
        this.displayId = params.displayId;
        this.productId = params.productId;
        this.$container = $(el);
        this.$btn = this.$container.find('.qarr-open-modal');

        this.addListener(this.$btn, 'click', 'openModal');
    },
    openModal: function openModal(e) {
        e.preventDefault();

        if (this.modal) {
            this.modal.$form.remove();
            delete this.modal;
            this.modal = new QarrFeedbackModal(this);
        } else {
            this.modal = new QarrFeedbackModal(this);
        }

        this.modal.on('onSaved', $.proxy(this, 'sendPayload'));
    },
    sendPayload: function sendPayload(data) {
        var targetModal = data.target;

        targetModal.$form.addClass('has-sent');
        targetModal.$header.find('span').html('Form Submitted!');
        targetModal.$body.html('<div class="qarr-modal-message">Your submission is being reviewed.</div>');
        targetModal.updateSizeAndPosition();

        setTimeout(function () {
            targetModal.hide();
        }, 3000);
    }
});

var QarrFeedbackModal = Garnish.Modal.extend({
    context: null,
    $form: null,
    $header: null,
    $body: null,
    $footer: null,

    init: function init(context) {
        var that = this;
        this.base(null, {
            shadeClass: 'modal-shade dark qarr-modal-shade'
        });
        this.context = context;

        var data = {
            type: context.type,
            displayId: context.displayId,
            productId: context.productId
        };

        data[QARR.csrfTokenName] = QARR.csrfTokenValue;

        $.post(QARR.actionUrl + 'qarr/frontend/get-modal-content', data, function (response, textStatus, jqXHR) {
            if (response.success) {
                that.initModal(response.template);
            }
        });
    },
    initModal: function initModal(template) {
        this.$template = template;
        this.setContainer(this.$template);
        this.show();

        this.$form = $('#qarr-modal-' + this.context.type);

        if (this.context.type === 'review') {
            new QarrStarRating(this.$form.find('.qarr-star-container'));
        }

        $.each(this.$form.find('.qarr-field'), function (i, el) {
            if ($(el).data('field-type') === 'checkboxes') {
                new QarrCheckboxes(el);
            }
            if ($(el).data('field-type') === 'radiobuttons') {
                new QarrRadioButtons(el);
            }
        });

        $.each(this.$form.find('.custom-field'), function (i, el) {
            if ($(el).hasClass('custom-textarea')) {
                new QarrTextareaField(el);
            } else {
                new QarrInputField(el);
            }
        });

        this.$header = this.$form.find('.qarr-header');
        this.$body = this.$form.find('.qarr-body');
        this.$footer = this.$form.find('.qarr-footer');
        this.$cancelBtn = this.$form.find('.cancel');

        this.addListener(this.$cancelBtn, 'click', 'hide');
        this.addListener(this.$form, 'submit', 'save');
    },
    save: function save(e) {
        e.preventDefault();
        var url = null;
        var that = this;
        var formData = this.$form.serialize();

        if (this.context.type === 'review') {
            url = QARR.actionUrl + 'qarr/reviews/save';
        } else {
            url = QARR.actionUrl + 'qarr/questions/save';
        }

        $.post(url, formData, function (response, textStatus, jqXHR) {
            if (response.success) {

                that.trigger('onSaved', {
                    data: 'we are submitting..'
                });
            } else {
                that.formValidation(response);
            }
        });
    },
    formValidation: function formValidation(response) {
        var that = this;

        $.each(response.errors, function (key, item) {
            $('#fields-' + key + '-' + that.context.type).addClass('has-error');
        });
    }
});

var QarrCheckboxes = Garnish.Base.extend({
    $container: null,
    $options: null,

    init: function init(container) {
        this.$container = $(container);
        this.$options = this.$container.find('input.checkbox');

        this.addListener(this.$options, 'change', 'onChange');
    },
    onChange: function onChange() {}
});

var QarrRadioButtons = Garnish.Base.extend({
    $container: null,
    $options: null,

    init: function init(container) {
        this.$container = $(container);
        this.$options = this.$container.find('input');

        this.addListener(this.$options, 'change', 'onChange');
    },
    onChange: function onChange(e) {
        var input = $(e.currentTarget);
        this.$container.find('label').removeClass('selected');
        input.parent().addClass('selected');
    }
});

var QarrInputField = Garnish.Base.extend({
    $el: null,
    $input: null,
    $label: null,
    $inputIcon: null,
    $noticeIcon: null,

    hadError: false,
    labelText: null,
    errorMessage: null,

    init: function init(el) {
        this.$el = $(el);
        this.$input = this.$el.find('input');
        this.$label = this.$el.find('label');
        this.$inputIcon = this.$el.find('.qarr-input-icon');
        this.$noticeIcon = this.$el.find('.qarr-notice-icon');
        this.labelText = this.$label.data('label');
        this.errorMessage = this.$label.data('error-message');

        this.addListener(this.$input, 'change', 'inputChange');
        this.addListener(this.$input, 'keyup', 'inputKeyup');
        this.addListener(this.$input, 'focus focusout', 'inputFocus');

        if (this.$el.hasClass('custom-select')) {
            this.addListener(this.$el.find('select'), 'change', 'inputChange');
            this.selectElement();
        }
    },
    selectElement: function selectElement() {
        var that = this;

        this.$el.on('select2:opening', function (e) {
            that.$el.addClass('has-focus');
        });

        this.$el.on('select2:closing', function (e) {
            that.$el.removeClass('has-focus');
        });
    },
    inputFocus: function inputFocus() {
        this.checkInputFocus();
    },
    inputKeyup: function inputKeyup() {
        var count = this.$input.val();

        if (!count && this.hadError) {
            this.$el.addClass('has-error');
            this.$label.html(this.errorMessage);
        }

        if (this.$el.hasClass('has-error')) {
            this.hadError = true;

            if (count) {
                this.$el.removeClass('has-error');
                this.$label.html(this.labelText);
            }
        }
    },
    inputChange: function inputChange() {
        this.checkInputValue();
    },
    checkInputFocus: function checkInputFocus() {
        if (this.$input.is(':focus')) {
            this.$el.addClass('has-focus');
        } else {
            this.$el.removeClass('has-focus');
        }

        this.checkInputValue();
    },
    checkInputValue: function checkInputValue() {
        var value = this.$input.val();

        if (this.$el.hasClass('custom-select')) {
            value = this.$el.find('select').select2('data');

            if (this.$el.hasClass('has-error')) {
                this.hadError = true;
            }

            if (this.hadError) {
                if (value) {
                    this.$el.removeClass('has-error');
                    this.$label.html(this.labelText);
                }
            }
        }

        if (value) {
            this.$el.addClass('has-value');
        } else {
            this.$el.removeClass('has-value');
        }
    }
});

var QarrTextareaField = Garnish.Base.extend({
    $el: null,
    $textarea: null,
    $label: null,
    $inputIcon: null,
    $noticeIcon: null,

    hadError: false,
    labelText: null,
    errorMessage: null,

    init: function init(el) {
        this.$el = $(el);
        this.$textarea = this.$el.find('textarea');
        this.$label = this.$el.find('label');
        this.$inputIcon = this.$el.find('.qarr-input-icon');
        this.$noticeIcon = this.$el.find('.qarr-notice-icon');
        this.labelText = this.$label.data('label');
        this.errorMessage = this.$label.data('error-message');

        this.addListener(this.$textarea, 'change', 'inputChange');
        this.addListener(this.$textarea, 'keyup', 'inputKeyup');
        this.addListener(this.$textarea, 'focus focusout', 'inputFocus');
    },
    inputFocus: function inputFocus() {
        this.checkInputFocus();
    },
    inputKeyup: function inputKeyup() {
        var count = this.$textarea.val();

        if (!count && this.hadError) {
            this.$el.addClass('has-error');
            this.$label.html(this.errorMessage);
        }

        if (this.$el.hasClass('has-error')) {
            this.hadError = true;

            if (count) {
                this.$el.removeClass('has-error');
                this.$label.html(this.labelText);
            }
        }
    },
    inputChange: function inputChange() {
        this.checkInputValue();
    },
    checkInputFocus: function checkInputFocus() {
        if (this.$textarea.is(':focus')) {
            this.$el.addClass('has-focus');
        } else {
            this.$el.removeClass('has-focus');
        }
    },
    checkInputValue: function checkInputValue() {
        var value = this.$textarea.val();

        if (value) {
            this.$el.addClass('has-value');
        } else {
            this.$el.removeClass('has-value');
        }
    }
});

var QarrStarRating = Garnish.Base.extend({
    $container: null,
    $star: null,
    $input: null,

    rating: null,

    init: function init(el) {
        this.$container = $(el);
        this.$star = this.$container.find('.qarr-star');
        this.$input = this.$container.find('input');

        this.addListener(this.$star, 'click', 'updateRating');
    },
    updateRating: function updateRating(e) {
        var currentStar = $(e.currentTarget);
        this.$star.removeClass('selected');
        this.$star.removeClass('active');

        this.rating = currentStar.data('star-count');
        currentStar.addClass('selected');
        currentStar.prevAll().addClass('active');

        this.$input.val(this.rating);
    }
});

var QarrPagination = Garnish.Base.extend({
    $pagerContainer: null,
    $loader: null,

    $pagerBtn: null,
    direction: null,

    $nextBtn: null,
    $prevBtn: null,

    style: null,
    type: null,
    limit: null,
    offset: null,
    totalPages: null,
    currentPage: null,

    productId: QARR.productId,

    init: function init(el) {
        this.$pagerContainer = $(el);
        this.$loader = $('.qarr-loader');

        this.$nextBtn = this.$pagerContainer.find('.qarr-pager-next');
        this.$prevBtn = this.$pagerContainer.find('.qarr-pager-prev');
        this.$pagerBtn = this.$pagerContainer.find('.qarr-pager');

        this.style = this.$pagerContainer.data('style');
        this.type = this.$pagerContainer.data('type');
        this.totalPages = this.$pagerContainer.data('total-pages');

        this.limit = parseInt(QARR.limit);
        this.offset = 0;
        this.currentPage = 1;

        this.addListener(this.$pagerBtn, 'click', 'loadPage');
    },
    loadPage: function loadPage(e) {
        e.preventDefault();
        var that = this;
        var $container = $('#qarr-' + this.type + '-container');

        this.direction = $(e.currentTarget).data('direction');
        this.setOffset();

        this.$loader.addClass('active');
        $container.addClass('transition');

        var payload = {
            type: this.type,
            limit: this.limit,
            offset: this.offset,
            productId: this.productId
        };

        payload[QARR.csrfTokenName] = QARR.csrfTokenValue;

        $.post(QARR.actionUrl + 'qarr/elements/query-elements', payload, function (response, textStatus, jqXHR) {
            if (response.success) {

                setTimeout(function () {
                    that.$loader.removeClass('active');

                    if (that.style === 'infinite') {
                        $container.append(response.template);
                    } else {
                        $container.html(response.template);
                    }

                    var entrySetId = $(response.template).attr('id');
                    var $entrySet = $('#' + entrySetId);

                    $('html, body').animate({
                        scrollTop: $entrySet.offset().top
                    }, 'fast');

                    $container.removeClass('transition');
                }, 1000);

                that.checkOffset();
            }
        });
    },
    setOffset: function setOffset() {
        if (this.direction === 'next') {
            this.offset = this.offset + this.limit;
            this.currentPage = this.currentPage + 1;
        } else {
            this.offset = this.offset - this.limit;
            this.currentPage = this.currentPage - 1;
        }
    },
    checkOffset: function checkOffset() {
        if (this.direction === 'next') {
            if (this.currentPage === this.totalPages) {
                this.$nextBtn.addClass('pager-disabled');
            }

            if (this.currentPage !== 1 || this.offset !== 0) {
                this.$prevBtn.removeClass('pager-disabled');
            }
        }

        if (this.direction === 'prev') {
            if (this.currentPage === 1 || this.offset === 0) {
                this.$prevBtn.addClass('pager-disabled');
            }

            if (this.currentPage !== this.totalPages) {
                this.$nextBtn.removeClass('pager-disabled');
            }
        }
    }
});

var QarrAnswerHud = Garnish.Base.extend({
    $container: null,
    questionId: null,
    authorId: null,
    authorName: null,

    $hudName: null,
    asCustomerText: null,
    asAnonymousText: null,

    anonymous: false,
    hud: null,

    init: function init(payload) {
        this.$container = $(payload.target);
        this.questionId = payload.questionId;
        this.authorId = payload.authorId;
        this.authorName = payload.authorName;

        this.getHud();
    },
    getHud: function getHud() {
        var that = this;

        var data = {
            id: this.questionId,
            author: {
                id: this.authorId,
                name: this.authorName
            }
        };

        data[QARR.csrfTokenName] = QARR.csrfTokenValue;

        $.post(QARR.actionUrl + 'qarr/answers/get-hud-modal', data, function (response, textStatus, jqXHR) {
            if (response.success) {

                that.createHud(response.template);
            }
        });
    },
    createHud: function createHud(template) {
        var that = this;

        this.hud = new Garnish.HUD(this.$container, template, {
            hudClass: 'hud qarr-hud',
            bodyClass: 'body',
            closeOtherHUDs: false
        });

        this.hud.on('hide', $.proxy(function () {
            delete this.hud;
            $('.hud-shade').remove();
            $('.qarr-hud').remove();
        }, this));

        this.$hudName = this.hud.$body.find('.hud-name');
        this.asCustomerText = this.$hudName.data('posting-customer');
        this.asAnonymousText = this.$hudName.data('posting-anonymous');

        this.hud.$body.find('textarea:first').trigger('focus').parent().addClass('has-focus');

        var textarea = new QarrTextareaField('.custom-textarea');
        var lightswitch = new QarrLightSwitch('.qarr-lightswitch');

        var $cancelBtn = this.hud.$footer.find('.cancel');

        this.addListener($cancelBtn, 'click', function () {
            this.hud.hide();
        });

        // Check Anonymous
        lightswitch.$outerContainer.on('change', function () {
            that.anonymous = lightswitch.$input.val() === '1';
            that.updateAnonymous();
        });

        this.hud.on('submit', function (e) {
            var anonymousValue = lightswitch.$input.val();
            var answerValue = that.hud.$body.find('textarea').val();

            if (answerValue === '') {
                textarea.$el.addClass('has-error');
            } else {
                textarea.$el.removeClass('has-error');

                // Submit Answer
                var data = {
                    questionId: that.questionId,
                    authorId: that.authorId,
                    anonymous: anonymousValue,
                    answer: answerValue
                };

                data[QARR.csrfTokenName] = QARR.csrfTokenValue;

                $.post(QARR.actionUrl + 'qarr/answers/save', data, function (response, textStatus, jqXHR) {
                    if (response.success) {
                        var _template = response.template;
                        that.hud.updateBody(_template);
                    }
                });
            }
        });
    },
    updateAnonymous: function updateAnonymous() {
        if (this.anonymous) {
            this.$hudName.find('span').html(this.asAnonymousText);
        } else {
            this.$hudName.find('span').html(this.asCustomerText);
        }
    }
});

var QarrAnswersContainer = Garnish.Base.extend({
    $container: null,
    $answersContainer: null,

    $cta: null,
    ctaText: null,
    hideText: null,

    visible: false,

    init: function init(el) {
        this.$container = $(el);
        this.$answersContainer = this.$container.find('.qarr-entry-more-answers-container');
        this.$cta = this.$container.find('a');
        this.ctaText = this.$cta.text();
        this.hideText = this.$cta.data('hide-text');

        this.addListener(this.$cta, 'click', 'showAnswers');
    },
    showAnswers: function showAnswers(e) {
        e.preventDefault();

        if (this.$container.hasClass('is-visible')) {
            this.$container.removeClass('is-visible');
            this.visible = false;
            this.animate();
        } else {
            this.$container.addClass('is-visible');
            this.visible = true;
            this.animate();
        }
    },
    animate: function animate() {
        var slideDir = this.visible ? 'slideDown' : 'slideUp';
        var dur = this.visible ? 200 : 400;

        this.$answersContainer.velocity('stop').velocity(slideDir, {
            easing: 'easeOutQuart',
            duration: dur,
            complete: $.proxy(function () {
                this.$cta.html(this.updateLinkText());
            }, this)
        });
    },
    updateLinkText: function updateLinkText() {
        if (this.visible) {
            this.$cta.html(this.hideText);
        } else {
            this.$cta.html(this.ctaText);
        }
    }
});

var QarrLightSwitch = Garnish.Base.extend({
    settings: null,
    $outerContainer: null,
    $innerContainer: null,
    $input: null,
    small: false,
    on: null,
    dragger: null,
    orientation: 'ltr',

    dragStartMargin: null,

    init: function init(outerContainer, settings) {
        this.$outerContainer = $(outerContainer);

        this.$outerContainer.data('lightswitch', this);

        this.small = this.$outerContainer.hasClass('small');

        this.setSettings(settings, QarrLightSwitch.defaults);

        this.$innerContainer = this.$outerContainer.find('.lightswitch-container:first');
        this.$input = this.$outerContainer.find('input:first');

        if (this.$input.prop('disabled')) {
            return;
        }

        this.on = this.$outerContainer.hasClass('on');

        this.$outerContainer.attr({
            'role': 'checkbox',
            'aria-checked': this.on ? 'true' : 'false'
        });

        this.addListener(this.$outerContainer, 'mousedown', '_onMouseDown');
        this.addListener(this.$outerContainer, 'keydown', '_onKeyDown');

        this.dragger = new Garnish.BaseDrag(this.$outerContainer, {
            axis: Garnish.X_AXIS,
            ignoreHandleSelector: null,
            onDragStart: $.proxy(this, '_onDragStart'),
            onDrag: $.proxy(this, '_onDrag'),
            onDragStop: $.proxy(this, '_onDragStop')
        });
    },
    turnOn: function turnOn() {
        this.$outerContainer.addClass('dragging');

        var animateCss = {};
        animateCss['margin-left'] = 0;
        this.$innerContainer.velocity('stop').velocity(animateCss, 200, $.proxy(this, '_onSettle'));

        this.$input.val(this.settings.value);
        this.$outerContainer.addClass('on');
        this.$outerContainer.attr('aria-checked', 'true');

        if (this.on !== (this.on = true)) {
            this.onChange();
        }
    },
    turnOff: function turnOff() {
        this.$outerContainer.addClass('dragging');

        var animateCss = {};
        animateCss['margin-left'] = this._getOffMargin();
        this.$innerContainer.velocity('stop').velocity(animateCss, 200, $.proxy(this, '_onSettle'));

        this.$input.val('');
        this.$outerContainer.removeClass('on');
        this.$outerContainer.attr('aria-checked', 'false');

        if (this.on !== (this.on = false)) {
            this.onChange();
        }
    },
    toggle: function toggle(event) {
        if (!this.on) {
            this.turnOn();
        } else {
            this.turnOff();
        }
    },
    onChange: function onChange() {
        this.trigger('change');
        this.settings.onChange();
        this.$outerContainer.trigger('change');
    },
    _onMouseDown: function _onMouseDown() {
        this.addListener(Garnish.$doc, 'mouseup', '_onMouseUp');
    },
    _onMouseUp: function _onMouseUp() {
        this.removeListener(Garnish.$doc, 'mouseup');

        if (!this.dragger.dragging) {
            this.toggle();
        }
    },
    _onKeyDown: function _onKeyDown(event) {
        switch (event.keyCode) {
            case Garnish.SPACE_KEY:
                {
                    this.toggle();
                    event.preventDefault();
                    break;
                }
            case Garnish.RIGHT_KEY:
                {
                    if (this.orientation === 'ltr') {
                        this.turnOn();
                    } else {
                        this.turnOff();
                    }

                    event.preventDefault();
                    break;
                }
            case Garnish.LEFT_KEY:
                {
                    if (this.orientation === 'ltr') {
                        this.turnOff();
                    } else {
                        this.turnOn();
                    }

                    event.preventDefault();
                    break;
                }
        }
    },


    _getMargin: function _getMargin() {
        return parseInt(this.$innerContainer.css('margin-left'));
    },

    _onDragStart: function _onDragStart() {
        this.$outerContainer.addClass('dragging');
        this.dragStartMargin = this._getMargin();
    },

    _onDrag: function _onDrag() {
        var margin = void 0;

        if (this.orientation === 'ltr') {
            margin = this.dragStartMargin + this.dragger.mouseDistX;
        } else {
            margin = this.dragStartMargin - this.dragger.mouseDistX;
        }

        if (margin < this._getOffMargin()) {
            margin = this._getOffMargin();
        } else if (margin > 0) {
            margin = 0;
        }

        this.$innerContainer.css('margin-left', margin);
    },

    _onDragStop: function _onDragStop() {
        var margin = this._getMargin();

        if (margin > this._getOffMargin() / 2) {
            this.turnOn();
        } else {
            this.turnOff();
        }
    },

    _onSettle: function _onSettle() {
        this.$outerContainer.removeClass('dragging');
    },

    destroy: function destroy() {
        this.base();
        this.dragger.destroy();
    },

    _getOffMargin: function _getOffMargin() {
        return this.small ? -9 : -11;
    }
}, {
    animationDuration: 100,
    defaults: {
        value: '1',
        onChange: $.noop
    }
});