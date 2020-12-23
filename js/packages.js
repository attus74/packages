Drupal.behaviors.packagesSearch = {
  attach: function (context, settings) {
    jQuery('#packages-home-search-wrapper').not('.initialized').each(function() {
      jQuery(this).addClass('initialized');
      var $input = jQuery(this).find('input#packages-home-search');
      $input.keyup(function() {
        var searchString = jQuery(this).val();
        var path = '/packages/home/search/' + searchString;
        Drupal.ajax({url: path}).execute();
      });
    });
  }
};