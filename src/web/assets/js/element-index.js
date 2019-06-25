Garnish.$doc.ready(function () {
  if (Craft.elementIndex) {
    Craft.elementIndex.statusMenu.$container.addClass('qarr-menu qarr-status-menu');
    Craft.elementIndex.sortMenu.$container.addClass('qarr-menu qarr-sort-menu');
    Craft.elementIndex.on('updateElements', function (e) {
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
