/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./development/js/utilities-web.js ***!
  \*****************************************/
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

QarrApi = {
  pathParam: 'p'
};
$.extend(QarrApi, {
  getActionUrl: function getActionUrl(path, params) {
    return QarrApi.getUrl(path, params, QARR.actionUrl);
  },
  escapeChars: function escapeChars(chars) {
    if (!Garnish.isArray(chars)) {
      chars = chars.split();
    }

    var escaped = '';

    for (var i = 0; i < chars.length; i++) {
      escaped += "\\" + chars[i];
    }

    return escaped;
  },
  ltrim: function ltrim(str, chars) {
    if (!str) {
      return str;
    }

    if (typeof chars === 'undefined') {
      chars = ' \t\n\r\0\x0B';
    }

    var re = new RegExp('^[' + QarrApi.escapeChars(chars) + ']+');
    return str.replace(re, '');
  },
  rtrim: function rtrim(str, chars) {
    if (!str) {
      return str;
    }

    if (typeof chars === 'undefined') {
      chars = ' \t\n\r\0\x0B';
    }

    var re = new RegExp('[' + QarrApi.escapeChars(chars) + ']+$');
    return str.replace(re, '');
  },
  trim: function trim(str, chars) {
    str = QarrApi.ltrim(str, chars);
    str = QarrApi.rtrim(str, chars);
    return str;
  },
  getUrl: function getUrl(path, params, baseUrl) {
    if (typeof path !== 'string') {
      path = '';
    } // Normalize the params


    var anchor = '';

    if ($.isPlainObject(params)) {
      var aParams = [];

      for (var name in params) {
        if (!params.hasOwnProperty(name)) {
          continue;
        }

        var value = params[name];

        if (name === '#') {
          anchor = value;
        } else if (value !== null && value !== '') {
          aParams.push(name + '=' + value);
        }
      }

      params = aParams;
    }

    if (Garnish.isArray(params)) {
      params = params.join('&');
    } else {
      params = QarrApi.trim(params, '&?');
    } // Was there already an anchor on the path?


    var apos = path.indexOf('#');

    if (apos !== -1) {
      // Only keep it if the params didn't specify a new anchor
      if (!anchor) {
        anchor = path.substr(apos + 1);
      }

      path = path.substr(0, apos);
    } // Were there already any query string params in the path?


    var qpos = path.indexOf('?');

    if (qpos !== -1) {
      params = path.substr(qpos + 1) + (params ? '&' + params : '');
      path = path.substr(0, qpos);
    } // Return path if it appears to be an absolute URL.


    if (path.search('://') !== -1 || path[0] === '/') {
      return path + (params ? '?' + params : '') + (anchor ? '#' + anchor : '');
    }

    path = QarrApi.trim(path, '/'); // Put it all together

    var url;

    if (baseUrl) {
      url = baseUrl;

      if (path && QarrApi.pathParam) {
        // Does baseUrl already contain a path?
        var pathMatch = url.match(new RegExp('[&\?]' + QarrApi.escapeRegex(QarrApi.pathParam) + '=[^&]+'));

        if (pathMatch) {
          url = url.replace(pathMatch[0], QarrApi.rtrim(pathMatch[0], '/') + '/' + path);
          path = '';
        }
      }
    } else {
      url = QarrApi.baseUrl;
    } // Does the base URL already have a query string?


    qpos = url.indexOf('?');

    if (qpos !== -1) {
      params = url.substr(qpos + 1) + (params ? '&' + params : '');
      url = url.substr(0, qpos);
    }

    if (!QarrApi.omitScriptNameInUrls && path) {
      if (QarrApi.usePathInfo || !QarrApi.pathParam) {
        // Make sure that the script name is in the URL
        if (url.search(QarrApi.scriptName) === -1) {
          url = QarrApi.rtrim(url, '/') + '/' + QarrApi.scriptName;
        }
      } else {
        // Move the path into the query string params
        // Is the path param already set?
        if (params && params.substr(0, QarrApi.pathParam.length + 1) === QarrApi.pathParam + '=') {
          var basePath,
              endPath = params.indexOf('&');

          if (endPath !== -1) {
            basePath = params.substring(2, endPath);
            params = params.substr(endPath + 1);
          } else {
            basePath = params.substr(2);
            params = null;
          } // Just in case


          basePath = QarrApi.rtrim(basePath);
          path = basePath + (path ? '/' + path : '');
        } // Now move the path into the params


        params = QarrApi.pathParam + '=' + path + (params ? '&' + params : '');
        path = null;
      }
    }

    if (path) {
      url = QarrApi.rtrim(url, '/') + '/' + path;
    }

    if (params) {
      url += '?' + params;
    }

    if (anchor) {
      url += '#' + anchor;
    }

    return url;
  },
  escapeRegex: function escapeRegex(str) {
    return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
  },
  sendActionRequest: function sendActionRequest(method, action, options) {
    var _this = this;

    return new Promise(function (resolve, reject) {
      options = options ? $.extend({}, options) : {};
      options.method = method;
      options.url = QarrApi.getActionUrl(action);
      options.headers = $.extend({
        'X-Requested-With': 'XMLHttpRequest'
      }, options.headers || {}, _this._actionHeaders());
      options.params = $.extend({}, options.params || {}, {
        // Force Safari to not load from cache
        v: new Date().getTime()
      });
      axios.request(options).then(resolve)["catch"](reject);
    });
  },
  postActionRequest: function postActionRequest(action, data, callback, options) {
    // Make 'data' optional
    if (typeof data === 'function') {
      options = callback;
      callback = data;
      data = {};
    }

    options = options || {};

    if (options.contentType && options.contentType.match(/\bjson\b/)) {
      if (_typeof(data) === 'object') {
        data = JSON.stringify(data);
      }

      options.contentType = 'application/json; charset=utf-8';
    }

    var jqXHR = $.ajax($.extend({
      url: QarrApi.getActionUrl(action),
      type: 'POST',
      dataType: 'json',
      headers: this._actionHeaders(),
      data: data,
      success: callback,
      error: function error(jqXHR, textStatus, errorThrown) {
        // Ignore incomplete requests, likely due to navigating away from the page
        // h/t https://stackoverflow.com/a/22107079/1688568
        if (jqXHR.readyState !== 4) {
          return;
        }

        alert('A server error occurred.');

        if (callback) {
          callback(null, textStatus, jqXHR);
        }
      }
    }, options)); // Call the 'send' callback

    if (typeof options.send === 'function') {
      options.send(jqXHR);
    }

    return jqXHR;
  },
  _actionHeaders: function _actionHeaders() {
    var headers = {};

    if (QARR.csrfTokenValue) {
      headers['X-CSRF-Token'] = QARR.csrfTokenValue;
    }

    return headers;
  }
});
QarrTabs = Garnish.Base.extend({
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
      elementId: this.reviewsContainer.data('element-id')
    };
    var questionParams = {
      type: 'question',
      displayId: this.questionsContainer.data('display-id'),
      elementId: this.questionsContainer.data('element-id')
    };
    this.reviewsContent = new QarrTabContent(this.reviewsContainer, this, 'review', reviewParams);
    this.questionsContent = new QarrTabContent(this.questionsContainer, this, 'question', questionParams); // Toggle tab content if local storage is set

    this.checkTabSelection();
    this.addListener(this.$tabLink, 'click', 'handleTabClick');
  },
  checkTabSelection: function checkTabSelection() {
    if (window.sessionStorage.getItem('qarr-tab')) {
      this.selectedTab = window.sessionStorage.getItem('qarr-tab');
    }

    this.updateTabSelection();
  },
  handleTabClick: function handleTabClick(e) {
    e.preventDefault();
    this.targetLink = $(e.currentTarget);
    this.target = this.targetLink.data('target');
    this.selectedTab = this.target;
    window.sessionStorage.setItem('qarr-tab', this.target);
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
QarrTabContent = Garnish.Base.extend({
  $container: null,
  $btn: null,
  type: null,
  displayId: null,
  elementId: null,
  modal: null,
  tabContext: null,
  init: function init(el, context, type, params) {
    this.tabContext = context;
    this.type = type;
    this.displayId = params.displayId;
    this.elementId = params.elementId;
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
    targetModal.$header.find('span').html(QARR.t.modal.success.title);
    targetModal.$body.html('<div class="qarr-modal-message">' + QARR.t.modal.success.message + '</div>');
    targetModal.updateSizeAndPosition();
    setTimeout(function () {
      targetModal.hide();
    }, 3000);
  }
});
QarrFeedbackModal = Garnish.Modal.extend({
  context: null,
  $form: null,
  $header: null,
  $body: null,
  $errorsContainer: null,
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
      elementId: context.elementId
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
    this.$errorsContainer = this.$container.find('.qarr-errors');
    this.$form = $('#qarr-modal-' + this.context.type);

    if (this.context.type === 'review') {
      new QarrStarRating(this.$form.find('.qarr-star-container'));
    } // $.each(this.$form.find('.qarr-field'), function (i, el) {
    //     if ($(el).data('field-type') === 'checkboxes') {
    //         new QarrCheckboxes(el);
    //     }
    //     if ($(el).data('field-type') === 'radiobuttons') {
    //         new QarrRadioButtons(el);
    //     }
    // });
    //
    // $.each(this.$form.find('.custom-field'), function (i, el) {
    //     if ($(el).hasClass('custom-textarea')) {
    //         new QarrTextareaField(el);
    //     } else {
    //         new QarrInputField(el);
    //     }
    // });


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
    var that = this; // let formData = this.$form.serialize()

    var formData = new FormData(this.$form[0]);

    if (this.context.type === 'review') {
      url = QARR.actionUrl + 'qarr/reviews/save';
    } else {
      url = QARR.actionUrl + 'qarr/questions/save';
    }

    $.ajax({
      url: url,
      type: 'POST',
      processData: false,
      contentType: false,
      cache: false,
      data: formData,
      success: function success(response) {
        if (response.success) {
          that.trigger('onSaved', {
            data: 'we are submitting..'
          });
        } else {
          Garnish.shake(that.$container);
          that.formValidation(response);
        }
      },
      error: function error(response) {
        Garnish.shake(that.$container);
      }
    });
  },
  formValidation: function formValidation(response) {
    var that = this;
    this.$errorsContainer.html('');
    $('.qarr-field').removeClass('has-error');
    $.each(response.errors, function (key, item) {
      that.$errorsContainer.append('<li>' + item + '</li>');
      $('#fields-' + key + '-' + that.context.type).addClass('has-error');
    });
    this.updateSizeAndPosition();
  }
});
QarrCheckboxes = Garnish.Base.extend({
  $container: null,
  $options: null,
  init: function init(container) {
    this.$container = $(container);
    this.$options = this.$container.find('input.checkbox');
    this.addListener(this.$options, 'change', 'onChange');
  },
  onChange: function onChange() {}
});
QarrRadioButtons = Garnish.Base.extend({
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
QarrInputField = Garnish.Base.extend({
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
QarrTextareaField = Garnish.Base.extend({
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
QarrStarRating = Garnish.Base.extend({
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
QarrPagination = Garnish.Base.extend({
  $paginationContainer: null,
  $entriesContainer: null,
  $sortSelector: null,
  $loader: null,
  $paginationLink: null,
  $nextBtn: null,
  $prevBtn: null,
  paginationStyle: null,
  direction: null,
  pageInfo: null,
  order: null,
  limit: null,
  type: null,
  elementType: null,
  elementId: null,
  offset: 0,
  currentPage: null,
  totalPages: null,
  init: function init(type) {
    this.$entriesContainer = $('#qarr-' + type + '-container');
    this.$paginationContainer = $('#qarr-' + type + '-pagination');
    this.$sortSelector = $('#qarr-' + type + '-sort');
    this.$loader = $('.qarr-loader');
    this.$paginationLink = this.$paginationContainer.find('.qarr-pager');
    this.$nextBtn = this.$paginationContainer.find('.qarr-pager-next');
    this.$prevBtn = this.$paginationContainer.find('.qarr-pager-prev');
    this.pageInfo = this.$entriesContainer.data('page-info');
    this.paginationStyle = this.$entriesContainer.data('pagination-style');
    this.totalPages = this.$entriesContainer.data('total-pages');
    this.currentPage = this.$entriesContainer.data('current-page');
    this.order = this.$sortSelector.val();
    this.limit = this.$entriesContainer.data('limit');
    this.offset = this.$entriesContainer.data('offset');
    this.elementType = this.$entriesContainer.data('element-type');
    this.elementId = this.$entriesContainer.data('element-id');
    this.initOffset();
    this.addListener(this.$paginationLink, 'click', 'handlePagination');
    this.addListener(this.$sortSelector, 'change', 'handleSortChange'); // TODO: use sessionStorage to track pagination for refresh
  },
  handlePagination: function handlePagination(e) {
    var _this2 = this;

    e.preventDefault();
    this.checkSorting();
    this.$loader.addClass('active');
    this.$entriesContainer.addClass('transition');
    this.direction = $(e.currentTarget).data('direction');
    this.setOffset();
    var params = {
      type: this.elementType,
      order: this.order,
      limit: this.limit,
      offset: this.offset,
      elementId: this.elementId
    };
    params[QARR.csrfTokenName] = QARR.csrfTokenValue;
    QarrApi.postActionRequest(QARR.actionUrl + 'qarr/elements/query-elements', params, function (response) {
      var template = response.template;

      _this2.$loader.removeClass('active');

      if (_this2.paginationStyle === 'infinite') {
        _this2.$entriesContainer.append(template);

        var entrySetId = $(template).attr('id');
        var $entrySet = $('#' + entrySetId);
        $('html, body').animate({
          scrollTop: $entrySet.offset().top
        }, 'fast');
      } else {
        _this2.$entriesContainer.html(template);
      } // Adding answers to questions


      _this2.$entriesContainer.find('.add-answer').on('click', function (e) {
        e.preventDefault();
        var answerParams = {
          target: $(this),
          questionId: $(this).data('id'),
          authorName: $(this).data('user-name'),
          authorId: $(this).data('user-id')
        };
        new QarrAnswerHud(answerParams);
      }); // Reporting abuse


      _this2.$entriesContainer.find('.qarr-entry-ra-btn').on('click', function (e) {
        e.preventDefault();
        var target = $(e.currentTarget);
        var abuseParams = {
          id: target.data('element-id'),
          type: target.data('type')
        };
        abuseParams[QARR.csrfTokenName] = QARR.csrfTokenValue;
        QarrApi.postActionRequest(QARR.actionUrl + 'qarr/elements/report-abuse', abuseParams, function (response) {
          if (response.success) {
            target.html("<span>" + QARR.t.abuse.success.button + "</span>");
          }
        });
      });

      _this2.$entriesContainer.removeClass('transition');

      _this2.checkOffset();
    });
  },
  handleSortChange: function handleSortChange() {
    this.resetPagination();
  },
  getPageNumberFromUrl: function getPageNumberFromUrl(url) {
    var pageTrigger = this.$entriesContainer.data('page-trigger');
    var pageNumberString = url.substring(url.lastIndexOf('/') + 1);
    return pageNumberString.replace(pageTrigger, '');
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
  resetPagination: function resetPagination() {
    this.currentPage = 1;
    this.offset = 0;
    this.$prevBtn.addClass('pager-disabled');

    if (this.currentPage < this.totalPages) {
      this.$nextBtn.removeClass('pager-disabled');
    }
  },
  initOffset: function initOffset() {
    if (this.currentPage) {
      this.$prevBtn.addClass('pager-disabled');
    }

    if (this.currentPage === this.totalPages) {
      this.$nextBtn.addClass('pager-disabled');
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
  },
  checkSorting: function checkSorting() {
    this.order = this.$sortSelector.val();
  }
});
QarrAnswerHud = Garnish.Base.extend({
  $container: null,
  $errorsContainer: null,
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
    this.$errorsContainer = this.hud.$body.find('.qarr-errors');
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
    }); // Check Anonymous

    lightswitch.$outerContainer.on('change', function () {
      that.anonymous = lightswitch.$input.val() === '1';
      that.updateAnonymous();
    });
    this.hud.on('submit', function (e) {
      var anonymousValue = lightswitch.$input.val();
      var answerValue = that.hud.$body.find('textarea').val();
      that.$errorsContainer.html('');
      $('.qarr-field').removeClass('has-error');

      if (answerValue === '') {
        Garnish.shake(that.hud.$body);
        textarea.$el.addClass('has-error');
        that.$errorsContainer.append('<li>Answer is required</li>');
      } else {
        textarea.$el.removeClass('has-error'); // Submit Answer

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
QarrStarFilterEntries = Garnish.Base.extend({
  $triggerEl: null,
  $entriesContainer: null,
  $sortSelector: null,
  type: null,
  elementType: null,
  limit: null,
  offset: null,
  rating: null,
  total: null,
  elementId: null,
  order: null,
  init: function init(el) {
    // TODO: Make this work with pagination and the rest of filters
    this.$loader = $('.qarr-loader');
    this.$triggerEl = $(el);
    this.type = this.$triggerEl.data('type');
    this.$entriesContainer = $('#qarr-' + this.type + '-container');
    this.$sortSelector = $('#qarr-' + this.type + '-sort');
    this.rating = this.$triggerEl.data('rating');
    this.total = this.$triggerEl.data('total');
    this.order = this.$sortSelector.val();
    this.limit = this.$entriesContainer.data('limit');
    this.offset = this.$entriesContainer.data('offset');
    this.elementId = this.$entriesContainer.data('element-id');
    this.elementType = this.$entriesContainer.data('element-type');
    this.addListener(this.$triggerEl, 'click', this.fetchEntries);
  },
  fetchEntries: function fetchEntries(e) {
    var _this3 = this;

    e.preventDefault();

    if (this.$triggerEl.hasClass('active')) {
      this.$triggerEl.removeClass('active');
      this.rating = null;
    } else {
      this.$triggerEl.addClass('active');
      this.rating = this.$triggerEl.data('rating');
    }

    this.$loader.addClass('active');
    this.$entriesContainer.addClass('transition');
    var params = {
      type: this.type,
      elementType: this.elementType,
      limit: this.limit,
      offset: this.offset,
      rating: this.rating,
      elementId: this.elementId,
      order: this.order
    };
    params[QARR.csrfTokenName] = QARR.csrfTokenValue;
    QarrApi.postActionRequest(QARR.actionUrl + 'qarr/elements/query-star-filtered-elements', params, function (response) {
      if (response.success) {
        setTimeout($.proxy(function () {
          this.$loader.removeClass('active');
          this.$entriesContainer.html(response.template);
          var entrySetId = $(response.template).attr('id');
          var $entrySet = $('#' + entrySetId);
          $('html, body').animate({
            scrollTop: $entrySet.offset().top
          }, 'fast');
          this.$entriesContainer.removeClass('transition');
        }, _this3), 1000); // Set sessionStorage for clicked star rating

        window.sessionStorage.setItem('qarr-star-filter', _this3.rating);
      }
    });
  }
});
QarrSortOrderEntries = Garnish.Base.extend({
  $selector: null,
  $container: null,
  $loader: null,
  selectedValue: null,
  elementType: null,
  type: null,
  limit: null,
  offset: null,
  elementId: null,
  init: function init(el) {
    this.$loader = $('.qarr-loader');
    this.$selector = $(el);
    this.type = this.$selector.data('type');
    this.$container = $('#qarr-' + this.type + '-container');
    this.elementType = this.$container.data('element-type');
    this.selectedValue = this.$selector.val();
    this.limit = this.$container.data('limit');
    this.offset = this.$container.data('offset');
    this.elementId = this.$container.data('elementId');
    this.addListener(this.$selector, 'change', this.handleSortChange);
  },
  handleSortChange: function handleSortChange() {
    var _this4 = this;

    this.selectedValue = this.$selector.val();
    this.$loader.addClass('active');
    this.$container.addClass('transition');
    var params = {
      order: this.selectedValue,
      type: this.elementType,
      limit: this.limit,
      offset: this.offset,
      elementId: this.elementId
    };
    params[QARR.csrfTokenName] = QARR.csrfTokenValue;
    QarrApi.postActionRequest(QARR.actionUrl + 'qarr/elements/query-sort-elements', params, function (response) {
      if (response.success) {
        setTimeout($.proxy(function () {
          this.$loader.removeClass('active');
          this.$container.html(response.template);
          var entrySetId = $(response.template).attr('id');
          var $entrySet = $('#' + entrySetId);
          $('html, body').animate({
            scrollTop: $entrySet.offset().top
          }, 'fast');
          this.$container.removeClass('transition');
        }, _this4), 1000); // Set sessionStorage for sort

        window.sessionStorage.setItem('qarr-sort-filter-type', _this4.elementType);
        window.sessionStorage.setItem('qarr-sort-filter-value', _this4.selectedValue); // TODO: Reset pagination
      }
    });
  }
});
QarrAnswersContainer = Garnish.Base.extend({
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
QarrLightSwitch = Garnish.Base.extend({
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
    var margin;

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
/******/ })()
;
