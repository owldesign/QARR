var QARR = {};
QARR.Widgets = {};
QARR.Widgets.StatusStats = Garnish.Base.extend({
  settings: null,
  data: null,
  $widget: null,
  $body: null,
  init: function init(widgetId, settings) {
    this.setSettings(settings);
    this.$widget = $('#widget' + widgetId);
    this.$body = this.$widget.find('.body:first');
  }
});
QARR.Widgets.PendingItemsWidget = Garnish.Base.extend({
  $container: null,
  $items: null,
  $currentItem: null,
  type: null,
  limit: null,
  dragger: null,
  exclude: null,
  init: function init(el) {
    var that = this;
    this.exclude = [];
    this.$container = $(el);
    this.limit = this.$container.data('limit');
    this.type = this.$container.data('type');
    this.$items = this.$container.find('.list-item:not(.excluded)');

    if (this.$items.length > 0) {
      $.each(this.$items, function (i, item) {
        var id = $(item).data('id');
        that.exclude.push(id);
        new QARR.Widgets.PendingItem(item, that);
      });
    }
  },
  addPendingItem: function addPendingItem(item) {
    new QARR.Widgets.PendingItem(item, this);
  },
  fetchNewExcludeIds: function fetchNewExcludeIds() {
    var that = this;
    this.exclude = [];
    $.each(this.$container.find('.list-item:not(.excluded)'), function (i, item) {
      var id = $(item).data('id');
      that.exclude.push(id);
    });
    return this.exclude;
  },
  fetchNewItem: function fetchNewItem() {
    var _this = this;

    var count = 1;
    var payload = {
      type: this.type,
      limit: count,
      exclude: this.fetchNewExcludeIds()
    };
    Craft.postActionRequest('qarr/elements/fetch-pending-items', payload, $.proxy(function (response, textStatus) {
      if (response.success) {
        var itemHtml = $(response.template);

        if (response.count === 0) {
          if (_this.fetchNewExcludeIds().length === 0) {
            var $empty = $('<div class="list-item excluded">' + '<div class="item-wrapper">' + '<p>' + Craft.t("qarr", "No recent submissions") + '</p>' + '</div>' + '</div>');

            _this.$container.find('.list').html(itemHtml); // this.$container.find('.list').html($empty);

          }
        } else {
          _this.$container.find('.list').append(itemHtml);

          itemHtml.addClass('new-item');

          _this.addPendingItem(_this.$container.find(itemHtml));
        }
      }
    }, this));
  }
});
QARR.Widgets.PendingItem = Garnish.Base.extend({
  $item: null,
  $itemWrapper: null,
  $progress: null,
  $actionBtn: null,
  $approveItemBtn: null,
  $deleteItemBtn: null,
  $rejectItemBtn: null,
  elementId: null,
  type: null,
  dragger: null,
  margin: null,
  direction: null,
  parent: null,
  hud: null,
  dragStartMargin: null,
  init: function init(el, parent) {
    this.parent = parent;
    this.$item = $(el);
    this.$progress = this.$item.find('.progress');
    this.$itemWrapper = this.$item.find('.item-wrapper');
    this.$actionBtn = this.$item.find('.action-btn');
    this.$approveItemBtn = this.$item.find('.approve-btn');
    this.$deleteItemBtn = this.$item.find('.delete-btn');
    this.$rejectItemBtn = this.$item.find('.reject-btn');
    this.elementId = this.$item.data('id');
    this.type = this.parent.type;
    this.dragger = new Garnish.BaseDrag(this.$item, {
      axis: Garnish.X_AXIS,
      onDragStart: $.proxy(this, '_onDragStart'),
      onDrag: $.proxy(this, '_onDrag'),
      onDragStop: $.proxy(this, '_onDragStop')
    });
    this.addListener(this.$actionBtn, 'click', 'performAction');
  },
  performAction: function performAction(e) {
    var that = this;
    e.preventDefault();
    var actionType = $(e.currentTarget).data('action-type');

    if (actionType === 'delete') {
      $form = $('<section class="hud-wrapper">' + '<label for="">' + Craft.t("qarr", "Are you sure?") + '</label>' + '</section>' + '<section class="hud-footer qarr-footer">' + '<input type="button" class="btn-modal cancel" value="' + Craft.t("qarr", "Cancel") + '">' + '<input type="submit" class="btn-modal submit" value="' + Craft.t("qarr", "Delete") + '"/>' + '</section>').appendTo(Garnish.$bod);
      this.hud = new Garnish.HUD(this.$deleteItemBtn, $form, {
        hudClass: 'hud qarr-hud',
        bodyClass: 'body',
        closeOtherHUDs: false
      });
      var $cancelBtn = this.hud.$footer.find('.cancel');
      this.addListener($cancelBtn, 'click', function () {
        this.hud.hide();

        this._putBack();
      });
      this.hud.on('submit', function (e) {
        that.deleteElement();
        that.hud.hide();
      });
    }

    if (actionType === 'reject') {
      Craft.postActionRequest('qarr/elements/update-status', this.buildPayload('rejected'), $.proxy(function (response, textStatus) {
        if (response.success) {
          Craft.cp.displayNotice(Craft.t('qarr', 'Item Rejected'));
          that.$rejectItemBtn.addClass('rejected');
          that.$item.addClass('reject-item');

          window.qarrnav._getPendingEntries();

          setTimeout(function () {
            that.parent.fetchNewItem();
            that.$item.remove();
          }, 500);
        }
      }, this));
    }
  },
  deleteElement: function deleteElement() {
    var that = this;
    var payload = {
      id: this.elementId
    };
    Craft.postActionRequest('qarr/reviews/delete', payload, $.proxy(function (response, textStatus) {
      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Item deleted'));
        that.$item.addClass('delete-item');

        window.qarrnav._getPendingEntries();

        setTimeout(function () {
          that.parent.fetchNewItem();
          that.$item.remove();
        }, 700);
      }
    }, this));
  },
  showPositiveMenu: function showPositiveMenu() {
    var that = this;
    Craft.postActionRequest('qarr/elements/update-status', this.buildPayload('approved'), $.proxy(function (response, textStatus) {
      if (response.success) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Item Approved'));
        that.$approveItemBtn.addClass('approved');
        that.$item.addClass('approve-item');
        setTimeout(function () {
          window.qarrnav._getPendingEntries();

          that.parent.fetchNewItem();
          that.$item.remove();
        }, 500);
      }
    }, this));
  },
  buildPayload: function buildPayload(status) {
    var payload = {
      id: this.elementId,
      status: status,
      type: this.type
    };
    return payload;
  },
  showNegativeMenu: function showNegativeMenu() {
    this.$item.addClass('other-menu');
  },
  _onSettle: function _onSettle() {
    this.$item.removeClass('dragging');
    this.$item.removeClass('other-menu');
  },
  _onDragStart: function _onDragStart() {
    this.$item.addClass('dragging');
    this.dragStartMargin = this._getPosition();
  },
  _onDrag: function _onDrag() {
    this.$item.removeClass('other-menu');
    var position;

    if (Craft.orientation === 'ltr') {
      position = this.dragStartMargin + this.dragger.mouseDistX;
    } else {
      position = this.dragStartMargin - this.dragger.mouseDistX;
    }

    if (position < this._getLeftPull()) {
      position = this._getLeftPull();
    } else if (position > 0) {
      if (position < this._getRightPull()) {
        position = this.dragStartMargin + this.dragger.mouseDistX;
      } else {
        position = this._getRightPull();
      }
    }

    this.$itemWrapper.css(Craft.left, position);
  },
  _onDragStop: function _onDragStop() {
    var position = this._getPosition();

    if (position === this._getLeftPull()) {
      this.showNegativeMenu();
    } else {
      if (position !== this._getRightPull()) {
        this._putBack();
      }
    }

    if (position === this._getRightPull()) {
      this.showPositiveMenu();
      this.$itemWrapper.css(Craft.left, this._getRightPull());
    }
  },
  _putBack: function _putBack() {
    this.$item.removeClass('dragging');
    this.$item.removeClass('other-menu');
    var animateCss = {};
    animateCss[Craft.left] = 0;
    this.$itemWrapper.velocity('stop').velocity(animateCss, QARR.Widgets.PendingItem.animationDuration);
  },
  _getLeftPull: function _getLeftPull() {
    return -200;
  },
  _getRightPull: function _getRightPull() {
    return 100;
  },
  _getPosition: function _getPosition() {
    return parseInt(this.$itemWrapper.css(Craft.left));
  }
}, {
  animationDuration: 100,
  defaults: {
    value: '1',
    onChange: $.noop
  }
}); // Garnish.$doc.ready(() => {
//     if ($('#widget-recent-reviews').length > 0) {
//         let pendingReviews = new QARR.Widgets.PendingItemsWidget('#widget-recent-reviews');
//     }
//
//     if ($('#widget-recent-questions').length > 0) {
//         let pendingQuestions = new QARR.Widgets.PendingItemsWidget('#widget-recent-questions');
//     }
// });
