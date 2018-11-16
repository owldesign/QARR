Garnish.$doc.ready(function () {
    console.log('UTILITIES!!');

    $('#update-geolocations').on('click', function (e) {
        e.preventDefault();

        Craft.postActionRequest('qarr/tools/utilities/update-geolocations', {}, $.proxy(function (response, textStatus) {
            console.log(response);
            if (response) {
                Craft.cp.displayNotice(Craft.t('qarr', response.message));
            }
        }, this));
    });
});
