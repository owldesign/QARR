Garnish.$doc.ready(function () {
    var reviewsChart = new QarrLineChart('.chart-explorer-container.chart-reviews', 'owldesign\\qarr\\elements\\Review');
    var questionsChart = new QarrLineChart('.chart-explorer-container.chart-questions', 'owldesign\\qarr\\elements\\Question');

    var reviewsDonutChart = new QarrDonutChart('#reviews-donut', 'owldesign\\qarr\\elements\\Review');
    var questionsDonutChart = new QarrDonutChart('#questions-donut', 'owldesign\\qarr\\elements\\Question');

    reviewsDonutChart.on('pieIn', function (e) {
        var mother = $('.reviews-charts');
        var target = mother.find('.stat-' + e.data.handle);
        target.addClass('has-hover');
    });

    reviewsDonutChart.on('pieOut', function (e) {
        $('.stat-item').removeClass('has-hover');
    });

    reviewsDonutChart.on('response', function (e) {
        var mother = $('.reviews-charts');
        $.each(e.data, function (i, item) {
            mother.find('.stat-' + item.handle).find('.stat-value').html(Math.round(item.percent * 100) + '%');
        });
    });

    questionsDonutChart.on('pieIn', function (e) {
        var mother = $('.questions-charts');
        var target = mother.find('.stat-' + e.data.handle);
        target.addClass('has-hover');
    });

    questionsDonutChart.on('pieOut', function (e) {
        $('.stat-item').removeClass('has-hover');
    });

    questionsDonutChart.on('response', function (e) {
        var mother = $('.questions-charts');
        $.each(e.data, function (i, item) {
            mother.find('.stat-' + item.handle).find('.stat-value').html(Math.round(item.percent * 100) + '%');
        });
    });
});

// if (response.total === 0) {
//     $('.stat-pending').find('.stat-value').html(response.total + '%');
//     $('.stat-approved').find('.stat-value').html(response.total + '%');
//     $('.stat-rejected').find('.stat-value').html(response.total + '%');
// } else {
//     $.each(response.data, function (i, item) {
//         $('.stat-' + item.handle).find('.stat-value').html(item.percent * 100 + '%');
//     });
// }
