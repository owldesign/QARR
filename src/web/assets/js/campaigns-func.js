var DirectLink = Garnish.Base.extend({
  $container: null,
  $elementSelectContainer: null,
  $generatedUrlInput: null,
  type: null,
  elementType: null,
  siteUrl: null,
  user: null,
  element: null,
  selectedClass: null,
  init: function init(container) {
    var that = this;
    this.$container = $(container);
    this.type = this.$container.data('type');
    this.elementType = this.$container.data('element-type');
    this.siteUrl = this.$container.data('site-url') + 'qarr/direct?';
    this.$elementSelectContainer = this.$container.find('.elementselect');
    this.$generatedUrlInput = this.$container.find('#generated-url'); // Handle selected elements

    this.handleSelectedElements();
    this.on('elementRemoved', function (e) {
      var type = e.settings.elementType;

      if (type === 'craft\\elements\\Entry') {
        that.element = null;
      }

      if (type === "craft\\elements\\User") {
        that.user = null;
      }

      that.$generatedUrlInput.val(that.siteUrl);
    });
  },
  handleSelectedElements: function handleSelectedElements() {
    var that = this;
    var elements = this.$container.find('.element');
    $.each(elements, function (key, el) {
      var target = $(el)[0];
      var id = target.dataset.id;
      var type = target.dataset.type;

      if (type === 'craft\\elements\\Entry') {
        that.element = {
          id: id,
          type: 'Entry'
        };
      }

      if (type === "craft\\elements\\User") {
        that.user = {
          id: id,
          type: 'User'
        };
      }
    });
  },
  handleAddElement: function handleAddElement(element) {
    var _this = this;

    Craft.postActionRequest('qarr/campaigns/direct-links/get-element-info', {
      elementId: element.id
    }, $.proxy(function (response, textStatus) {
      if (response.success) {
        _this.buildUrl(response);
      }
    }, this));
  },
  handleRemoveElement: function handleRemoveElement() {},
  buildUrl: function buildUrl(data) {
    this.selectedClass = data["class"];
    var oldUrl = this.$generatedUrlInput.val();
    var elementClass = data["class"];
    var element = data.element;

    if (elementClass === 'User') {
      this.user = element;
    } else {
      this.element = element;
    }

    if (this.user && this.element) {
      var newUrl = this.siteUrl + 'elementId=' + this.element.id + '&userId=' + this.user.id;
      this.$generatedUrlInput.val(newUrl);
    } else {
      this.$generatedUrlInput.val(oldUrl);
    }
  }
});
