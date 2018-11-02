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
});
