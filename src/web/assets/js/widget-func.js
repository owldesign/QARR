var QARR = {};
QARR.Widgets = {};
QARR.Widgets.PendingItemsWidget = Garnish.Base.extend({
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
        new QARR.Widgets.PendingItem(item, parent);
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
    new QARR.Widgets.PendingItem(item, this);
    this.checkElements();
  }
});
QARR.Widgets.PendingItem = Garnish.Base.extend({
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
      var $form = $('<section class="hud-wrapper">' + '<label for="">' + Craft.t("qarr", "Are you sure?") + '</label>' + '</section>' + '<section class="hud-footer qarr-footer">' + '<button type="button" class="qarr-btn btn-small cancel">' + Craft.t("qarr", "Cancel") + '</button>' + '<button type="submit" class="qarr-btn btn-purple btn-small submit">' + Craft.t("qarr", "Delete") + '</button>' + '</section>').appendTo(Garnish.$bod);
      this.hud = new Garnish.HUD(e.target, $form, {
        hudClass: 'hud qarr-hud',
        bodyClass: 'body',
        closeOtherHUDs: false
      });
      var $cancelBtn = this.hud.$footer.find('.cancel');
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
