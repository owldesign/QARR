Garnish.$doc.ready(function () {
    console.log('RULES!!');

    $('#ruleData').select2({
        tags: true,
        tokenSeparators: [',', '.']
    });

    // $('#ruleData').on('select2:open', function(e) {
    // });

    // $('.tag-link').on('click', function(e) {
    //     let target = $($(this).data('target'))
    //     let field = $(this).data('field');
    //
    //     target.val(target.val() + field);
    //     target.focus();
    // });
});
