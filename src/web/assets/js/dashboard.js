Garnish.$doc.ready(function () {
  var reviewsChart = new QarrLineChart('.chart-explorer-container.chart-reviews', 'owldesign\\qarr\\elements\\Review');
  var questionsChart = new QarrLineChart('.chart-explorer-container.chart-questions', 'owldesign\\qarr\\elements\\Question');
  var reviewsDonutChart = new QarrDonutChart('#reviews-donut', 'owldesign\\qarr\\elements\\Review');
  var questionsDonutChart = new QarrDonutChart('#questions-donut', 'owldesign\\qarr\\elements\\Question');
  reviewsDonutChart.on('pieIn', function (e) {
    var mother = $('#widget-reviews');
    var target = mother.find('.stat-' + e.data.handle);
    target.addClass('has-hover');
  });
  reviewsDonutChart.on('pieOut', function (e) {
    $('.stat-item').removeClass('has-hover');
  });
  reviewsDonutChart.on('response', function (e) {
    var mother = $('#widget-reviews');
    $.each(e.data, function (i, item) {
      mother.find('.stat-' + item.handle).find('.stat-value').html(Math.round(item.percent * 100) + '%');
    });
  });
  questionsDonutChart.on('pieIn', function (e) {
    var mother = $('#widget-questions');
    var target = mother.find('.stat-' + e.data.handle);
    target.addClass('has-hover');
  });
  questionsDonutChart.on('pieOut', function (e) {
    $('.stat-item').removeClass('has-hover');
  });
  questionsDonutChart.on('response', function (e) {
    var mother = $('#widget-questions');
    $.each(e.data, function (i, item) {
      mother.find('.stat-' + item.handle).find('.stat-value').html(Math.round(item.percent * 100) + '%');
    });
  });

  if ($('#widget-top-country-submissions').length > 0) {
    $('.reset-geolocation-stats').on('click', function (e) {
      e.preventDefault();
      Craft.postActionRequest('qarr/geolocations/reset', {}, $.proxy(function (response, textStatus) {
        if (response) {
          window.location.reload();
        }
      }, this));
    });
    var tip = tippy('.tippy-with-html', {
      onShow: function onShow(e) {
        var id = e.id;
        var template = document.getElementById('country-details-' + id).cloneNode(true);
        $(template).show();
        e.setContent(template);
      },
      // onHide(e) {
      //     return false;
      // },
      placement: 'top',
      interactive: true,
      theme: 'light',
      duration: 400,
      arrow: true
    });
  }
});
