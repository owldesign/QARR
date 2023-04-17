/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***************************************!*\
  !*** ./development/js/widget-func.js ***!
  \***************************************/
QARRWidgets = {};
QARRWidgets.PendingItemsWidget = Garnish.Base.extend({
  $container: null,
  $items: null,
  $loader: null,
  payload: null,
  exclude: null,
  init: function init(el) {
    var parent = this;
    this.exclude = [];
    this.$container = $(el);
    this.$loader = this.$container.find('.loader');
    this.$items = this.$container.find('.widget-recent-element-item');
    if (this.$items.length > 0) {
      $.each(this.$items, function (i, item) {
        new QARRWidgets.PendingItem(item, parent);
      });
    }
    this.checkElements();
  },
  checkElements: function checkElements() {
    this.$items = this.$container.find('.widget-recent-element-item');
    if (this.$items.length === 0) {
      this.$container.find('.element-list').html('<p class="text-gray-500">' + Craft.t('qarr', 'No pending submissions') + '</p>');
    }
  },
  excludeElement: function excludeElement(id) {
    this.exclude.push(id);
  },
  fetchNewItem: function fetchNewItem(payload, oldChild) {
    var _this = this;
    var newPayload = {
      type: payload.type,
      limit: 1,
      exclude: this.exclude
    };
    Craft.postActionRequest('qarr/elements/fetch-pending-items', newPayload, $.proxy(function (response, textStatus) {
      _this.$loader.addClass('hidden');
      if (response.success) {
        oldChild.remove();
        var itemHtml = $(response.template);
        _this.$container.find('.element-list').append(itemHtml);
        itemHtml.addClass('element-item-new');
        _this.addPendingItem(_this.$container.find(itemHtml));
      }
    }, this));
  },
  addPendingItem: function addPendingItem(item) {
    new QARRWidgets.PendingItem(item, this);
    this.checkElements();
  }
});
QARRWidgets.PendingItem = Garnish.Base.extend({
  $item: null,
  $actionBtn: null,
  parent: null,
  payload: null,
  hud: null,
  init: function init(el, parent) {
    this.parent = parent;
    this.$item = $(el);
    this.$actionBtn = this.$item.find('.action-btn');
    this.payload = {
      id: this.$item.data('element-id'),
      type: this.$item.data('type')
    };
    this.parent.excludeElement(this.payload.id);
    this.addTip();
    this.addListener(this.$actionBtn, 'click', 'performAction');
  },
  addTip: function addTip() {
    var tippyTarget = this.$item.find('.tippy-with-html');
    tippy(tippyTarget[0], {
      onShow: function onShow(e) {
        var id = e.reference.dataset.tippyId;
        var template = document.getElementById('element-popup-' + id).cloneNode(true);
        $(template).show();
        e.setContent(template);
      },
      placement: 'top',
      interactive: true,
      theme: 'light',
      duration: 400,
      arrow: true
    });
  },
  performAction: function performAction(e) {
    var _this2 = this;
    var that = this;
    var action = e.target.dataset.actionType;
    if (action === 'delete') {
      var $hudContents = $();
      var $form = $('<div/>');
      var $footer = $('<div class="hud-footer"/>').appendTo($form);
      var $body = $("\n                <div>".concat(Craft.t("qarr", "Are you sure?"), "</div>\n            ")).appendTo($form);
      var $buttonsContainer = $('<div class="buttons right"/>').appendTo($footer);
      var $cancelBtn = $('<div class="btn">' + Craft.t('qarr', 'Cancel') + '</div>').appendTo($buttonsContainer);
      var $okBtn = $('<input class="btn submit" type="submit" value="' + Craft.t('qarr', 'Ok') + '"/>').appendTo($buttonsContainer);
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
      Craft.postActionRequest('qarr/elements/update-status', this.payload, $.proxy(function (response, textStatus) {
        if (response.success) {
          Craft.cp.displayNotice(Craft.t('qarr', 'Item ' + _this2.payload.status));
          that.$item.addClass('status-changed');
          _this2.$item.velocity('slideUp', {
            duration: 300
          });
          _this2.parent.fetchNewItem(that.payload, _this2.$item);
        }
      }, this));
    }
  },
  deleteElement: function deleteElement() {
    var _this3 = this;
    this.parent.$loader.removeClass('hidden');
    var newPayload = {
      id: this.payload.id
    };
    Craft.postActionRequest('qarr/elements/delete', newPayload, $.proxy(function (response, textStatus) {
      _this3.parent.$loader.addClass('hidden');
      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Item deleted'));
        _this3.$item.addClass('item-deleted');
        _this3.$item.velocity('slideUp', {
          duration: 300
        });
        _this3.parent.fetchNewItem(_this3.payload, _this3.$item);
      }
    }, this));
  }
});
/******/ })()
;