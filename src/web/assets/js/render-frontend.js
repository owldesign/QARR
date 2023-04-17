/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************************!*\
  !*** ./development/js/render-frontend.js ***!
  \*******************************************/
// Bawe
QR.FormModal = {};

// Element Index
QR.ElementIndex = Garnish.Base.extend({
  store: null,
  initialized: false,
  elementType: null,
  customTemplate: null,
  loader: null,
  // Elements
  $elements: null,
  elements: null,
  totalResults: null,
  resultSet: null,
  // Sort/Order
  sort: null,
  order: null,
  // Pagination
  pagination: null,
  page: 1,
  // Review Form
  reviewForm: null,
  init: function init(id, elementType, settings) {
    this.store = store.namespace('qarr');
    this.setSettings(JSON.parse(settings), QR.ElementIndex.defaults);

    // Elements
    this.$elements = $('[data-qarr-elements]');
    this.elements = this.$elements ? new QR.ElementsView(this.$elements, this) : null;
    this.elementType = elementType;

    // Sort
    var $sort = $('[data-qarr-sort]');
    this.sort = $sort ? new QR.Sort($sort) : null;
    if (this.sort) {
      this.sort.on('sortselected', $.proxy(this, '_handleSortChange'));
    }

    // Pagination
    var $pagination = $('[data-qarr-pagination]');
    this.pagination = $pagination ? new QR.Pagination($pagination, this) : null;
    if (this.pagination) {
      this.pagination.on('pageInfoReady', this._paginationIsReady);
    }

    // Loader
    this.loader = new QR.Loader(this);

    // Review Form
    var $reviewForm = $('[data-qarr-review-form]');
    this.reviewForm = $reviewForm ? new QR.ReviewForm($reviewForm, this) : null;

    // Start
    this.initialized = true;
    this.afterInit();
    this.setPage();
    this.updateElements();
  },
  afterInit: function afterInit() {
    this.onAfterInit();
  },
  updateElements: function updateElements() {
    var _this = this;
    this.loader.show();
    if (!this.initialized) {
      return;
    }
    var params = this.getViewParams();
    QRAPI.sendActionRequest('POST', this.settings.updateElementsAction, {
      data: params
    }).then(function (response) {
      _this._updateView(params, response.data);
      _this.loader.hide();
    })["catch"](function (e) {
      console.log('A server error occurred: ', e);
    });
  },
  setPage: function setPage(page) {
    if (page) {
      page = Math.max(page, 1);
    } else {
      page = this.getPage();
    }
    this.page = page;
    this.store.session.set('page', this.page);
  },
  getPage: function getPage() {
    // Can store current page for page refresh
    // let page = this.store.session.get('page')
    // return Math.max(page, 1)

    return 1;
  },
  getViewParams: function getViewParams() {
    var criteria = {
      offset: this.settings.limit * (this.page - 1),
      limit: this.settings.limit
    };
    $.extend(criteria, this.settings.criteria);
    this.store.session.set('criteria', this.settings.criteria);
    var params = {
      elementType: this.elementType,
      criteria: criteria
    };
    this.trigger('registerViewParams', {
      params: params
    });
    return params;
  },
  onAfterInit: function onAfterInit() {
    this.settings.onAfterInit();
    this.trigger('afterInit');
  },
  _updateView: function _updateView(params, response) {
    var _this2 = this;
    this._countResults().then(function (total) {
      var first = Math.min(_this2.settings.limit * (_this2.page - 1) + 1, total);
      var last = Math.min(first + (_this2.settings.limit - 1), total);
      var pageInfo = {
        first: first,
        last: last,
        total: total,
        totalPages: Math.max(Math.ceil(total / _this2.settings.limit), 1),
        currentPage: _this2.page
      };

      // Update pagination
      if (_this2.pagination) {
        _this2.pagination.setupPageInfo(pageInfo);
      }
    })["catch"](function () {
      _this2.loader.hide();
    });
    if (this.pagination) {
      if (this.pagination.paginationStyle === 'infinite') {
        this.$elements.append(response.html);
      } else {
        this.$elements.html(response.html);
      }
    } else {
      this.$elements.html(response.html);
    }
  },
  _countResults: function _countResults() {
    var _this3 = this;
    return new Promise(function (resolve, reject) {
      if (_this3.totalResults !== null) {
        resolve(_this3.totalResults);
      } else {
        var params = _this3.getViewParams();
        delete params.criteria.offset;
        delete params.criteria.limit;
        delete params.criteria.order;
        if (_this3.resultSet === null) {
          _this3.resultSet = Math.floor(Math.random() * 100000000);
        }
        params.resultSet = _this3.resultSet;
        QRAPI.sendActionRequest('POST', _this3.settings.countElementsAction, {
          data: params
        }).then(function (response) {
          if (response.data.resultSet === _this3.resultSet) {
            _this3.totalResults = response.data.count;
            resolve(response.data.count);
          } else {
            reject();
          }
        })["catch"](reject);
      }
    });
  },
  _handleSortChange: function _handleSortChange(event) {
    $.extend(this.settings.criteria, {
      order: $(event)[0].selectedSort
    });
    this.setPage(1);
    this.updateElements();
  },
  _paginationIsReady: function _paginationIsReady(event) {
    var pageInfo = event.pageInfo;
  }
}, {
  defaults: {
    updateElementsAction: 'qarr/render-elements/get-elements',
    countElementsAction: 'qarr/render-elements/count-elements',
    onAfterInit: $.noop,
    criteria: null,
    limit: 1
  }
});

// Elements View
QR.ElementsView = Garnish.Base.extend({
  context: null,
  $container: null,
  limit: null,
  _totalVisible: null,
  init: function init($container, context, settings) {
    this.context = context;
    this.$container = $container;
    this.limit = $container.data('limit');
    this.setSettings(settings, QR.ElementsView.defaults);
    var $elements = this.$container.children();
    this._totalVisible = $elements.length;
    this.afterInit();
  },
  afterInit: function afterInit() {
    this.onAfterInit();
  },
  onAfterInit: function onAfterInit() {
    this.settings.onAfterInit();
    this.trigger('afterInit');
  },
  onAppendElements: function onAppendElements($newElements) {
    this.settings.onAppendElements($newElements);
    this.trigger('appendElements', {
      newElements: $newElements
    });
  }
}, {
  defaults: {
    params: null,
    onAppendElements: $.noop,
    onAfterInit: $.noop
  }
});

// Pagination
QR.Pagination = Garnish.Base.extend({
  context: null,
  $container: null,
  $paginationLink: null,
  paginationStyle: 'arrows',
  pageInfo: null,
  $pagesContainer: null,
  init: function init($container, context) {
    this.context = context;
    this.$container = $container;
    this.paginationStyle = this.$container.data('pagination-style');
    this.$prevBtn = this.$container.find('.qarr-pager-prev');
    this.$nextBtn = this.$container.find('.qarr-pager-next');
    this.$paginationLink = this.$container.find('.qarr-pager');
  },
  setupPageInfo: function setupPageInfo(pageInfo) {
    this.pageInfo = pageInfo;
    this.onPageInfoReady();
    if (this.paginationStyle === 'text') {
      this.$pagesContainer = this.$container.find('.qarr-pages');
      this.updatePageNumbers();
    }
    this.removeListener(this.$prevBtn, 'click');
    this.removeListener(this.$nextBtn, 'click');
    if (this.context.page === 1) {
      this.$prevBtn.addClass('pager-disabled');
    } else {
      this.$prevBtn.removeClass('pager-disabled');
    }
    if (pageInfo.currentPage === pageInfo.totalPages) {
      this.$nextBtn.addClass('pager-disabled');
    } else {
      this.$nextBtn.removeClass('pager-disabled');
    }
    if (this.context.page > 1) {
      this.addListener(this.$prevBtn, 'click', function () {
        this.removeListener(this.$prevBtn, 'click');
        this.removeListener(this.$nextBtn, 'click');
        this.context.setPage(this.context.page - 1);
        this.context.updateElements();
      });
    }
    if (this.context.page < pageInfo.totalPages) {
      this.addListener(this.$nextBtn, 'click', function () {
        this.removeListener(this.$prevBtn, 'click');
        this.removeListener(this.$nextBtn, 'click');
        this.context.setPage(this.context.page + 1);
        this.context.updateElements();
      });
    }
  },
  updatePageNumbers: function updatePageNumbers() {
    // Clear page numbers
    this.$pagesContainer.html('');

    // Add new pages
    for (var i = 0; i < this.pageInfo.totalPages; i++) {
      var page = i + 1;
      this.$pagesContainer.append("<a class=\"qarr-pager qarr-pager-text ".concat(this.pageInfo.currentPage === page ? 'qarr-current-page' : '', "\" data-direction=\"mixed\" data-page=\"").concat(page, "\">").concat(page, "</a>"));
    }
    var $pages = this.$pagesContainer.children();
    $pages.on('click', $.proxy(function (el) {
      var $target = $(el.currentTarget);
      if (!$target.hasClass('qarr-current-page')) {
        var _page = $target.data('page');
        this.context.setPage(_page);
        this.context.updateElements();
      }
    }, this));
  },
  onPageInfoReady: function onPageInfoReady() {
    this.trigger('pageInfoReady', {
      pageInfo: this.pageInfo
    });
  }
}, {
  defaults: {
    onPageInfoReady: $.noop
  }
});

// Sort
QR.Sort = Garnish.Base.extend({
  context: null,
  $container: null,
  init: function init($container, context) {
    this.context = context;
    this.$container = $container;
    this.addListener(this.$container, 'change', this.handleSortChange);
  },
  handleSortChange: function handleSortChange() {
    this.trigger('sortselected', {
      selectedSort: this.$container.val()
    });
  }
});

// Loader
QR.Loader = Garnish.Base.extend({
  context: null,
  $container: null,
  init: function init(context) {
    this.context = context;
    this.$container = $('<div class="qarr-line-progress-bar qarr-loader"></div>').hide();
    if (this.context.pagination.paginationStyle === 'text') {
      this.$container.addClass('smaller');
    }
  },
  show: function show() {
    if ($('.qarr-loader').length > 0) {
      this.$container.show();
    } else {
      this.context.$elements.after(this.$container);
      this.$container.show();
    }
  },
  hide: function hide() {
    this.$container.hide();
  },
  destroy: function destroy() {
    this.$container.remove();
    delete this;
  }
});

// Leave Review Modal
QR.ReviewForm = Garnish.Base.extend({
  $container: null,
  context: null,
  modal: null,
  displayHandle: null,
  elementId: null,
  init: function init($container, context) {
    this.context = context;
    this.$container = $container;
    this.displayHandle = this.$container.data('display') !== '' ? this.$container.data('display') : null;
    this.elementId = context.settings.criteria.elementId;
    this.type = context.elementType;
    this.addListener(this.$container, 'click', this.handleFormModal);
  },
  handleFormModal: function handleFormModal() {
    if (!this.modal) {
      new QR.FormModal.ReviewModal(this);
    } else {
      this.modal.show();
    }
  }
});
QR.FormModal.ReviewModal = Garnish.Modal.extend({
  context: null,
  $form: null,
  $header: null,
  $body: null,
  $errorsContainer: null,
  $footer: null,
  init: function init(context, settings) {
    var _this4 = this;
    this.context = context;
    this.base(null, {
      shadeClass: 'modal-shade dark qarr-modal-shade'
    });
    this.setSettings(settings, QR.FormModal.ReviewModal);
    var data = {
      elementType: context.type,
      displayHandle: context.displayHandle,
      elementId: context.elementId
    };
    data[QRAPI.csrfTokenName] = QRAPI.csrfTokenValue;
    QRAPI.sendActionRequest('POST', this.settings.defaults.getModalTemplateAction, {
      data: data
    }).then(function (response) {
      _this4.initModal(response.data.template);
    })["catch"](function (e) {
      console.log('A server error occurred: ', e);
    });
  },
  initModal: function initModal(template) {
    this.setContainer(template);
    this.$form = this.$container.find('form');
    this.$errorsContainer = this.$container.find('.qarr-errors');
    this.show();
    new QR.FormModal.Stars(this);
    this.$header = this.$container.find('.qarr-header');
    this.$body = this.$container.find('.qarr-body');
    this.$footer = this.$container.find('.qarr-footer');
    this.$cancelBtn = this.$container.find('.cancel');
    this.addListener(this.$cancelBtn, 'click', 'hide');
    this.addListener(this.$form, 'submit', 'save');
  }
}, {
  defaults: {
    getModalTemplateAction: 'qarr/render-elements/get-modal-content',
    onModalInit: $.noop
  }
});
QR.FormModal.Stars = Garnish.Base.extend({
  context: null,
  init: function init(context) {
    this.context = context;
    console.log('STARS');
  }
});

//
// Garnish.$doc.ready(function() {
//     new QR.ElementIndex()
// })
/******/ })()
;