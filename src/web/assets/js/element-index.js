/******/ (() => { // webpackBootstrap
/*!*****************************************!*\
  !*** ./development/js/element-index.js ***!
  \*****************************************/
Garnish.$doc.ready(function () {
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Element Index
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  $('.customize-sources').on('mouseenter', function () {
    $('#sources').addClass('active');
  }).on('mouseleave', function () {
    $('#sources').removeClass('active');
  });
  $('.configure-elements').on('mouseenter', function () {
    $('.element-element').addClass('active');
  }).on('mouseleave', function () {
    $('.element-element').removeClass('active');
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Configure Elements
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.configure-elements').on('click', function (e) {
    e.preventDefault();
    new ConfigureElementsModal();
  });
  $('.elementindex').on('click', '.configure-element', function (e) {
    e.preventDefault();
    var target = $(this).data('target');
    var type = $(this).data('type');
    new ConfigureElementsModal(target, type);
  });

  if (Craft.elementIndex) {
    Craft.elementIndex.statusMenu.$container.addClass('qarr-menu qarr-status-menu');
    Craft.elementIndex.sortMenu.$container.addClass('qarr-menu qarr-sort-menu');
    Craft.elementIndex.on('updateElements', function (e) {
      $('.configure-element').on('click', function (e) {
        e.preventDefault();
        new ConfigureElementHud($(this));
      });

      if (Craft.elementIndex.view.elementSelect) {
        var count = Craft.elementIndex.view._totalVisible;

        if (count === 0) {
          console.log('no elements');
          $('.elementindex .elements').html('<div class="noelements">' + Craft.t('qarr', 'No entries available.' + '</div>'));
        }
      }
    });
  }
});
/******/ })()
;