var QARR = {};
QARR.Widgets = {};

QARR.Widgets.StatusStats = Garnish.Base.extend({
    settings: null,
    data: null,

    $widget: null,
    $body: null,

    init: function init(widgetId, settings) {
        this.setSettings(settings);

        this.$widget = $('#widget' + widgetId);
        this.$body = this.$widget.find('.body:first');

        console.log(this.$body);
    }
});

Garnish.$doc.ready(function () {

    // let reviewsDonutChart = new QarrDonutChart('.reviews-donut', 'owldesign\\qarr\\elements\\Review');
    //
    // reviewsDonutChart.on('pieIn', function (e) {
    //     let mother = $('.qarr-widget');
    //     let target = mother.find('.stat-' + e.data.handle);
    //     target.addClass('has-hover');
    // });
    //
    // reviewsDonutChart.on('pieOut', function (e) {
    //     $('.stat-item').removeClass('has-hover');
    // });
    //
    // reviewsDonutChart.on('response', function (e) {
    //     let mother = $('.qarr-widget');
    //     $.each(e.data, function(i, item) {
    //         mother.find('.stat-' + item.handle).find('.stat-value').html(Math.round(item.percent * 100) + '%');
    //     });
    // });
});