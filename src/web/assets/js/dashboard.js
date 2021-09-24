/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************************!*\
  !*** ./development/js/dashboard.js ***!
  \*************************************/
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
  }); // if ($('#widget-top-country-submissions').length > 0) {
  //
  //     $('.reset-geolocation-stats').on('click', function(e) {
  //         e.preventDefault();
  //
  //         Craft.postActionRequest('qarr/geolocations/reset', {}, $.proxy(((response, textStatus) => {
  //             if (response) {
  //                 window.location.reload();
  //             }
  //         }), this))
  //     });
  //
  //
  //     const tip = tippy('.tippy-with-html', {
  //         onShow(e) {
  //             const id = e.id;
  //             const template = document.getElementById('country-details-'+id).cloneNode(true);
  //             $(template).show();
  //             e.setContent(template);
  //         },
  //         // onHide(e) {
  //         //     return false;
  //         // },
  //         placement: 'top',
  //         interactive: true,
  //         theme: 'light',
  //         duration: 400,
  //         arrow: true
  //     });
  // }
});
/******/ })()
;