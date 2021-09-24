/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************************!*\
  !*** ./development/js/render-frontend.js ***!
  \*******************************************/
QR.ElementIndex = Garnish.Base.extend({
  store: null,
  initialized: false,
  elementType: null,
  // Elements
  $elements: null,
  elements: null,
  // Sort
  sort: null,
  // Pagination
  pagination: null,
  page: 1,
  criteria: null,
  customTemplate: null,
  init: function init(id, elementType, criteria, customTemplate) {
    this.store = store.namespace('qarr');
    this.criteria = JSON.parse(criteria);
    this.setSettings({}, QR.ElementIndex.defaults); // Custom Template

    if (customTemplate) {
      this.customTemplate = customTemplate;
    } // Elements


    this.$elements = $('[data-qarr-elements]');
    this.elements = this.$elements ? new QR.ElementsView(this.$elements, this) : null;
    this.elementType = elementType; // Sort

    var $sort = $('[data-qarr-sort]');
    this.sort = $sort ? new QR.Sort($sort) : null;

    if (this.sort) {
      this.sort.on('sortselected', $.proxy(this, '_handleSortChange'));
    } // Pagination


    var $pagination = $('[data-qarr-pagination]');
    this.pagination = $pagination ? new QR.Pagination($pagination, this) : null;
    this.initialized = true;
    this.afterInit(); // this.setPage(1)

    this.updateElements();
  },
  afterInit: function afterInit() {
    this.onAfterInit();
  },
  _handleSortChange: function _handleSortChange(event) {
    var sortValue = $(event)[0].selectedSort; // this.setSortValue($option.val())

    console.log('Sort: ', sortValue);
    this.updateElements();
  },
  updateElements: function updateElements() {
    var _this = this;

    if (!this.initialized) {
      return;
    }

    var params = this.getViewParams();
    console.log('Params: ', params);
    QRAPI.sendActionRequest('POST', this.settings.updateElementsAction, {
      data: params
    }).then(function (response) {
      _this._updateView(params, response.data);
    })["catch"](function (e) {
      console.log('A server error occurred: ', e);
    });
  },
  getViewParams: function getViewParams() {
    return {
      elementType: this.elementType,
      customTemplate: this.customTemplate,
      criteria: this.criteria
    };
  },
  onAfterInit: function onAfterInit() {
    this.settings.onAfterInit();
    this.trigger('afterInit');
  },
  _updateView: function _updateView(params, response) {
    this.$elements.html(response.html);
  }
}, {
  defaults: {
    updateElementsAction: 'qarr/render-elements/get-elements',
    onAfterInit: $.noop
  }
}); // Elements View

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
}); // Pagination

QR.Pagination = Garnish.Base.extend({
  context: null,
  $container: null,
  paginationStyle: 'arrows',
  init: function init($container, context) {
    this.context = context;
    this.$container = $container;
    this.paginationStyle = this.$container.data('pagination-style');
  }
}); // QR.Pagination = Garnish.Base.extend({
//     $container: null,
//
//     entriesContainer: null,
//     loader: null,
//
//     pagination: false,
//     paginationStyle: 'arrows',
//     $paginationLink: null,
//     $nextBtn: null,
//     $prevBtn: null,
//     pageTrigger: null,
//
//     pageInfo: null,
//     currentPage: null,
//     page: null,
//     totalPages: null,
//     order: "dateCreated desc",
//     direction: null,
//
//     element: null,
//
//     // TODO: move all element related queries to EntriesContainer
//     // TODO: only keep pagination stuff here
//
//     init(id, entriesContainer) {
//         this.$container = $(`#${id}`)
//         this.$paginationLink = this.$container.find('.qarr-pager')
//         this.paginationStyle = this.$container.data('pagination-style')
//         this.$nextBtn = this.$container.find('.qarr-pager-next')
//         this.$prevBtn = this.$container.find('.qarr-pager-prev')
//         this.pageTrigger = this.$container.data('page-trigger')
//
//         this.pageInfo = this.$container.data('page-info')
//         this.currentPage = this.pageInfo.currentPage
//         this.totalPages = this.pageInfo.totalPages
//         this.pagination = this.$container.data('pagination')
//
//         // Entries Container Instance
//         this.entriesContainer = new QR.EntriesContainer($(`#${entriesContainer}`))
//         this.element = this.entriesContainer.getElementInfo()
//
//         // Loader Instance
//         this.loader = new QR.Loader(this)
//
//         this.initOffset()
//
//         this.addListener(this.$paginationLink, 'click', this.handlePagination)
//     },
//
//     handlePagination(e) {
//         e.preventDefault()
//
//         this.loader.show()
//         this.entriesContainer.transition()
//         this.direction = $(e.currentTarget).data('direction')
//
//         let params = {
//             type: this.element.type,
//             elementId: this.element.id,
//             order: this.order,
//             limit: this.element.limit,
//             template: this.element.template,
//             pagination: true.pagination,
//         }
//
//         if (this.paginationStyle === 'text') {
//             if (this.direction === 'mixed') {
//                 this.page = $(e.currentTarget).data('page')
//                 this.currentPage = this.page
//                 $(e.currentTarget).addClass('qarr-current-page')
//             }
//         }
//
//         this.setOffset()
//
//         params[QR.csrfTokenName] = QR.csrfTokenValue;
//         params.offset = this.getOffset()
//
//         QRAPI.postActionRequest(QRAPI.getActionUrl() + 'qarr/elements/query-render-elements', params, res => {
//             if (res.success) {
//                 this.loader.hide()
//                 this.entriesContainer.setContainer(res.template)
//                 this.entriesContainer.transition(false)
//
//                 if (this.paginationStyle === 'text') {
//                     this.updatePaginationStyles(e)
//                 }
//
//                 this.checkOffset()
//             }
//         })
//     },
//
//     updatePaginationStyles(e) {
//         $('.qarr-pager').removeClass('qarr-current-page')
//         let target = this.$container.find(`[data-page="${this.currentPage}"]`)
//         target.addClass('qarr-current-page')
//     },
//
//     initQueryElements() {
//         let params = {
//             type: this.element.type,
//             elementId: this.element.id,
//             order: this.order,
//             limit: this.element.limit,
//             offset: this.getOffset(),
//             template: this.element.template,
//             pagination: true.pagination,
//         }
//
//         params[QR.csrfTokenName] = QR.csrfTokenValue;
//
//         QRAPI.postActionRequest(QRAPI.getActionUrl() + 'qarr/elements/query-render-elements', params, res => {
//             if (res.success) {
//                 this.loader.hide()
//                 this.entriesContainer.setContainer(res.template)
//                 this.entriesContainer.transition(false)
//
//                 this.checkOffset()
//
//                 this.updatePaginationStyles()
//             }
//         })
//     },
//
//     getOffset() {
//         if (this.currentPage > 1) {
//             return (this.currentPage - 1) * this.element.limit
//         } else {
//             return 0
//         }
//     },
//
//     initOffset() {
//         // let pageInfo = JSON.parse(window.sessionStorage.getItem('qr-page-info'))
//         let pageInfo = store.session.get('QRPageInfo')
//
//
//         if (!pageInfo) {
//             // window.sessionStorage.setItem('qr-page-info', JSON.stringify(this.pageInfo))
//             store.session.set('QRPageInfo', this.pageInfo)
//
//             if (this.currentPage) {
//                 this.$prevBtn.addClass('pager-disabled')
//             }
//         } else {
//             if (this.currentPage !== pageInfo.currentPage) {
//                 this.currentPage = pageInfo.currentPage
//                 // Query correct records
//                 this.initQueryElements()
//             } else {
//                 if (this.currentPage === 1) {
//                     this.$prevBtn.addClass('pager-disabled')
//                 }
//             }
//         }
//
//         if (this.currentPage === this.totalPages) {
//             this.$nextBtn.addClass('pager-disabled')
//         }
//     },
//
//     checkOffset() {
//         if (this.direction === 'next') {
//             if (this.currentPage === this.totalPages) {
//                 this.$nextBtn.addClass('pager-disabled');
//             }
//
//             if (this.currentPage !== 1 || this.getOffset() !== 0) {
//                 this.$prevBtn.removeClass('pager-disabled');
//             }
//         }
//
//         if (this.direction === 'prev') {
//             if (this.currentPage === 1 || this.getOffset() === 0) {
//                 this.$prevBtn.addClass('pager-disabled');
//             }
//
//             if (this.currentPage !== this.totalPages) {
//                 this.$nextBtn.removeClass('pager-disabled');
//             }
//         }
//
//         if (this.direction === 'mixed') {
//             if (this.currentPage === 1 || this.getOffset() === 0) {
//                 this.$prevBtn.addClass('pager-disabled');
//             } else {
//                 this.$prevBtn.removeClass('pager-disabled');
//             }
//
//             if (this.currentPage !== this.totalPages) {
//                 this.$nextBtn.removeClass('pager-disabled');
//             } else {
//                 this.$nextBtn.addClass('pager-disabled');
//             }
//         }
//     },
//
//     setOffset() {
//         // let pageInfo = JSON.parse(window.sessionStorage.getItem('qr-page-info'))
//         let pageInfo = store.session.get('QRPageInfo')
//
//         if (this.direction === 'next') {
//             this.element.offset = this.element.offset + this.element.limit;
//             this.currentPage = this.currentPage + 1;
//             pageInfo.currentPage = this.currentPage
//         } else if (this.direction === 'prev') {
//             this.element.offset = this.element.offset - this.element.limit;
//             this.currentPage = this.currentPage - 1;
//             pageInfo.currentPage = this.currentPage
//         } else {
//             this.element.offset = this.element.offset - this.element.limit;
//             this.currentPage = this.page;
//             pageInfo.currentPage = this.page
//         }
//
//         store.session.set('QRPageInfo', pageInfo)
//         // window.sessionStorage.setItem('qr-page-info', JSON.stringify(pageInfo))
//     },
// })
// Entries

QR.Entries = Garnish.Base.extend({
  $container: null,
  template: null,
  id: null,
  type: null,
  limit: null,
  offset: 0,
  paginationStyle: 'arrows',
  // TODO: take a look at BaseElementIndex.js class
  // TODO: It has all the goodies we can use to create getViewParams as well us updateElements() methods
  // TODO: Take a look at setPage() method
  init: function init(payload) {// this.$container = payload
    //
    // this.template = this.$container.data('template')
    // this.id = this.$container.data('element-id')
    // this.type = this.$container.data('element-type')
    // this.limit = this.$container.data('limit')
    // this.paginationStyle = this.$container.data('pagination-style')
    //
    // this.$container.data('container', this)
  },
  transition: function transition(active) {
    if (active) {
      this.$container.addClass('in-transition');
    } else {
      this.$container.removeClass('in-transition');
    }
  },
  getElementInfo: function getElementInfo() {
    return {
      id: this.id,
      type: this.type,
      limit: parseInt(this.limit),
      offset: this.offset,
      template: this.template
    };
  },
  querySortedElements: function querySortedElements() {
    console.log('query elements');
  },
  setContainer: function setContainer(html) {
    var top = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

    if (this.paginationStyle === 'infinite') {
      this.$container.append(html);
      var entriesListId = $(html).attr('id');
      var $entriesList = $('#' + entriesListId);
      $('html, body').animate({
        scrollTop: $entriesList.offset().top + top
      }, 'fast');
    } else {
      this.$container.html(html);
    }
  }
}); // Sort

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
}); // Loader

QR.Loader = Garnish.Base.extend({
  context: null,
  $loader: null,
  init: function init(context) {
    this.context = context;
    this.$loader = $('<div class="qarr-gradient-spinner qarr-loader"></div>').hide();
    parent.$container.find('> div').append(this.$loader);

    if (this.context.pagination.paginationStyle === 'text') {
      this.$loader.addClass('smaller');
    }
  },
  show: function show() {
    this.$loader.show();
  },
  hide: function hide() {
    this.$loader.hide();
  },
  destroy: function destroy() {
    this.$loader.remove();
    delete this;
  }
}); //
// Garnish.$doc.ready(function() {
//     new QR.ElementIndex()
// })
/******/ })()
;