/******/ (() => { // webpackBootstrap
/*!*********************************!*\
  !*** ./development/js/rules.js ***!
  \*********************************/
Garnish.$doc.ready(function () {
  // Data List
  if ($('#rule-data').length > 0) {
    var dataInput = document.getElementById('rule-data');
    var tagify = new Tagify(dataInput);
    tagify.DOM.input.classList.add('tagify__input--outside');
    tagify.DOM.scope.parentNode.insertBefore(tagify.DOM.input, tagify.DOM.scope);
    document.querySelector('.tags--removeAllBtn').addEventListener('click', function (e) {
      tagify.removeAllTags();
      $('.tags--removeAllBtn').addClass('btn-disabled');
    }.bind(tagify));
    tagify.on('add', function (e) {
      if (tagify.value.length > 0) {
        $('.tags--removeAllBtn').removeClass('btn-disabled');
      }
    });
    tagify.on('remove', function (e) {
      if (tagify.value.length === 0) {
        $('.tags--removeAllBtn').addClass('btn-disabled');
      }
    });
  } // Delete Rule


  $('#delete-rule').on('click', function (e) {
    e.preventDefault();
    var data = {
      id: $(this).data('id')
    };
    var message = Craft.t('qarr', 'Deleting this rule will also remove all its flagged entries?');
    var deletePrompt = new QarrPrompt(message, null);
    deletePrompt.on('response', function (response) {
      var _this = this;

      if (response && response.response === 'ok') {
        Craft.postActionRequest('qarr/rules/delete', data, $.proxy(function (response, textStatus) {
          if (response.success) {
            setTimeout($.proxy(function () {
              Craft.cp.displayNotice(Craft.t('qarr', 'Rule deleted, redirecting...'));
              setTimeout($.proxy(function () {
                Craft.redirectTo(Craft.getCpUrl() + '/qarr/rules');
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