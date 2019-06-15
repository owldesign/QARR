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
    this.$promptMessage.html(message);
    var $cancelButton = $('<div class="btn">' + Craft.t('qarr', 'Cancel') + '</div>').appendTo(this.$promptButtons),
        $submitBtn = $('<input type="submit" class="btn submit" value="' + Craft.t('qarr', 'OK') + '" />').appendTo(this.$promptButtons);

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
