(function ($, Drupal, drupalSettings) {
    $('.created-time-ago').each(function () {
        var date = $(this).data('datetime');
        // console.log(date);
        date = moment(date, moment.ISO_8601).fromNow();
        // console.log(date);
        // Now we have to extract the number
        var matches = date.match(/^([0-9]+)(.*)$/);
        if (matches === null) {
            // String with a/an, which we can add to Drupal.t in its entirety
            $(this).html(Drupal.t('Posted ' + date));
        } else {
            // console.log(matches);
            $(this).html(Drupal.t('Posted @number' + matches[2], {'@number': matches[1]}));
        }
    })
})(jQuery, Drupal, drupalSettings);