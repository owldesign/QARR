var DirectLink = Garnish.Base.extend({
  $container: null,
  $elementSelectContainer: null,
  $generatedUrlInput: null,
  type: null,
  elementType: null,
  siteUrl: null,
  selectedElement: null,
  selectedUser: null,
  init: function init(container) {
    this.$container = $(container);
    this.type = this.$container.data('type');
    this.elementType = this.$container.data('element-type');
    this.siteUrl = this.$container.data('site-url');
    this.$elementSelectContainer = this.$container.find('.elementselect');
    this.$generatedUrlInput = this.$container.find('#generated-url'); // Element Selector

    this.elementSelector(); // User Selector

    this.userSelector();
  },
  elementSelector: function elementSelector() {
    var that = this;
    var elementType = 'craft\\elements\\Entry';

    if (this.type === 'product') {
      elementType = 'craft\\commerce\\elements\\Product';
    }

    var elementSelector = new ElementSelect.ElementSelectInput({
      id: 'element-select',
      name: 'element',
      elementType: elementType,
      limit: 1,
      editable: false,
      sortable: true,
      onSelectElements: $.proxy(function (element) {
        that.elementTargetSelected(element);
      }, this),
      onRemoveElements: $.proxy(this, 'handleElementRemove')
    });
  },
  elementTargetSelected: function elementTargetSelected(element) {
    this.selectedElement = {
      id: element[0].id,
      label: element[0].label,
      siteId: element[0].siteId,
      status: element[0].status,
      url: element[0].url
    };
    this.buildUrl();
  },
  handleElementRemove: function handleElementRemove() {
    this.selectedElement = null;
    this.buildUrl();
  },
  userSelector: function userSelector() {
    var that = this;
    var userSelector = new ElementSelect.ElementSelectInput({
      id: 'element-select-user',
      name: 'user',
      elementType: "craft\\elements\\User",
      limit: 1,
      editable: false,
      sortable: true,
      onSelectElements: $.proxy(function (element) {
        that.userTargetSelected(element);
      }, this),
      onRemoveElements: $.proxy(this, 'handleUserRemove')
    });
  },
  handleUserRemove: function handleUserRemove() {
    this.selectedUser = null;
    this.buildUrl();
  },
  userTargetSelected: function userTargetSelected(element) {
    this.selectedUser = {
      id: element[0].id,
      label: element[0].label,
      siteId: element[0].siteId,
      status: element[0].status,
      url: element[0].url
    };
    this.buildUrl();
  },
  buildUrl: function buildUrl() {
    var userString = '';
    var elementString = '';
    var newUrl = '';
    var oldUrl = this.$generatedUrlInput.val();

    if (this.selectedUser) {
      userString = 'userId=' + this.selectedUser.id;
    }

    if (this.selectedElement) {
      elementString = 'elementId=' + this.selectedElement.id;
    }

    if (userString !== '') {
      if (elementString !== '') {
        newUrl = this.siteUrl + 'qarr/direct?' + userString + '&' + elementString;
      } else {
        newUrl = this.siteUrl + 'qarr/direct?' + userString;
      }
    } else {
      newUrl = this.siteUrl + 'qarr/direct?' + elementString;
    }

    this.$generatedUrlInput.val(newUrl);
  }
});
var ElementSelect = {};
ElementSelect.ElementSelectInput = Craft.BaseElementSelectInput.extend({}); // var idParam;
//
// if (Garnish.isArray(this.userId)) {
//     idParam = ['and'];
//
//     for (var i = 0; i < this.userId.length; i++) {
//         idParam.push('not ' + this.userId[i]);
//     }
// }
// else {
//     idParam = 'not ' + this.userId;
// }
//
// this.userSelect = new Craft.BaseElementSelectInput({
//     id: 'transferselect' + this.id,
//     name: 'transferContentTo',
//     elementType: 'craft\\elements\\User',
//     criteria: {
//         id: idParam
//     },
//     limit: 1,
//     modalSettings: {
//         closeOtherModals: false
//     },
//     onSelectElements: $.proxy(function() {
//         this.updateSizeAndPosition();
//
//         if (!this.$deleteActionRadios.first().prop('checked')) {
//             this.$deleteActionRadios.first().trigger('click');
//         }
//         else {
//             this.validateDeleteInputs();
//         }
//     }, this),
//     onRemoveElements: $.proxy(this, 'validateDeleteInputs'),
//     selectable: false,
//     editable: false
// });
//
// this.addListener($cancelBtn, 'click', 'hide');
//
// this.addListener(this.$deleteActionRadios, 'change', 'validateDeleteInputs');
// this.addListener($form, 'submit', 'handleSubmit');
//
// this.base($form, settings);
