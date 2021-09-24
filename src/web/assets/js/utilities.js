/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************************!*\
  !*** ./development/js/utilities.js ***!
  \*************************************/
Garnish.$doc.ready(function () {
  console.log('UTILITIES!!');
  $('#update-geolocations').on('click', function (e) {
    e.preventDefault();
    Craft.postActionRequest('qarr/settings/utilities/update-geolocations', {}, $.proxy(function (response, textStatus) {
      console.log(response);

      if (response) {
        Craft.cp.displayNotice(Craft.t('qarr', response.message));
      }
    }, this));
  });
  $('#fix-review-elements').on('click', function (e) {
    e.preventDefault();
    Craft.postActionRequest('qarr/settings/utilities/fix-review-elements', {}, $.proxy(function (response, textStatus) {
      console.log(response);

      if (response) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Review entries fixed'));
      }
    }, this));
  });
  $('#fix-question-elements').on('click', function (e) {
    e.preventDefault();
    Craft.postActionRequest('qarr/settings/utilities/fix-question-elements', {}, $.proxy(function (response, textStatus) {
      console.log(response);

      if (response) {
        Craft.cp.displayNotice(Craft.t('qarr', 'Question entries fixed'));
      }
    }, this));
  });
});
/******/ })()
;