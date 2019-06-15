Garnish.$doc.ready(function () {
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Tabs
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  new QarrTabs('#qarr-display-container'); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Report Abuse
  // TODO: this needs to work for ajax pagination as well
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.qarr-entry-ra-btn').on('click', function (e) {
    e.preventDefault();
    var that = $(this);
    var payload = {
      id: $(this).data('element-id'),
      type: $(this).data('type')
    };
    data[QARR.csrfTokenName] = QARR.csrfTokenValue;
    $.post(QARR.actionUrl + 'qarr/elements/report-abuse', payload, function (response, textStatus, jqXHR) {
      if (response.success) {
        that.parent().html('<span>Reported</span>');
      }
    });
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Star Changer
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.qarr-star:not(.static)').on('click', function () {
    $('.qarr-star').removeClass('selected');
    $('.qarr-star').removeClass('active');
    var rating = $(this).data('star-count');
    $(this).addClass('selected');
    $(this).prevAll().addClass('active');
    $('#qarr-rating-input').val(rating);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Pagination
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  var qarrPaginations = $('.qarr-pagination');
  [].forEach.call(qarrPaginations, function (el) {
    new QarrPagination(el);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Questions
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  $('.add-answer').on('click', function (e) {
    e.preventDefault();
    var payload = {
      target: $(this),
      questionId: $(this).data('id'),
      authorName: $(this).data('user-name'),
      authorId: $(this).data('user-id')
    };
    var answerHud = new QarrAnswerHud(payload);
  }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // Star Filter
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

  var qarrFilterItem = $('.qarr-filter-item');
  [].forEach.call(qarrFilterItem, function (el) {
    var payload = {
      target: $(el),
      type: $(el).data('type'),
      rating: $(el).data('rating'),
      total: $(el).data('total-entries')
    };
    new QarrStarFilterEntries(payload);
  }); // Sort Filter

  $('.qarr-entry-sort').on('change', function (e) {
    var $loader = $('.qarr-loader');
    var $container = $('#qarr-' + $(this).data('type') + '-container');
    var value = $(this).val();
    var type = $(this).data('type');
    $loader.addClass('active');
    $container.addClass('transition');
    var payload = {
      value: value,
      type: type,
      limit: QARR.limit,
      elementId: QARR.elementId
    };
    payload[QARR.csrfTokenName] = QARR.csrfTokenValue;
    $.post(QARR.actionUrl + 'qarr/elements/query-sort-elements', payload, function (response, textStatus, jqXHR) {
      if (response.success) {
        setTimeout(function () {
          $loader.removeClass('active');
          $container.html(response.template);
          var entrySetId = $(response.template).attr('id');
          var $entrySet = $('#' + entrySetId);
          $('html, body').animate({
            scrollTop: $entrySet.offset().top
          }, 'fast');
          $container.removeClass('transition'); // TODO: add dynamic pagination

          $('.qarr-pagination').hide();
        }, 1000); // Set localstorage for clicked star rating

        window.localStorage.setItem('qarr-sort-filter-type', type);
        window.localStorage.setItem('qarr-sort-filter-value', value);
      }
    });
  });
});
