/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!************************************!*\
  !*** ./development/js/displays.js ***!
  \************************************/
Garnish.$doc.ready(function () {
  // Title Format
  $('.tag-link').on('click', function () {
    var target = $($(this).data('target'));
    var field = $(this).data('field');
    target.val(target.val() + field);
    target.focus();
  });

  // Delete Display
  $('#delete-display').on('click', function (e) {
    e.preventDefault();
    var data = {
      id: $(this).data('id')
    };
    var message = Craft.t('qarr', 'Are you sure you want to remove this display?');
    var deletePrompt = new QarrPrompt(message, null);
    deletePrompt.on('response', function (response) {
      var _this = this;
      if (response && response.response === 'ok') {
        Craft.postActionRequest('qarr/displays/delete', data, $.proxy(function (response, textStatus) {
          if (response.success) {
            setTimeout($.proxy(function () {
              Craft.cp.displayNotice(Craft.t('qarr', 'Display deleted, redirecting...'));
              setTimeout($.proxy(function () {
                Craft.redirectTo(Craft.getCpUrl() + '/qarr/displays');
              }, this), 1000);
            }, _this), 1000);
          }
        }, this));
      }
    });
  });
});
/******/ })()
;