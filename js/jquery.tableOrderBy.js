/**
 * @plugin tableOrderBy
 * @version 1.1, Nov 03th, 2016
 * @author Michael Mifsud
 * @source This plugin template is from http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 *
 * This plugin is designed to be used in conjunction with the \Tk\Table\Cell\OrderBy object
 * to make the table rows dragable
 *
 * Note: The main node to use as the selector is the parent of the cells
 *       that you want to be sortable. IE: 'table' or 'table tbody'
 *
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').tableOrderBy({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('tableOrderBy').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('tableOrderBy').settings.foo;
 *
 *   });
 * </code>
 */
(function($) {
  /**
   *
   * @param element
   * @param options
   */
  var tableOrderBy = function(element, options) {


    // Current instance of the object
    var plugin = this;
    // reference to the jQuery version of DOM element
    var $element = $(element);
    // this plugins current settings
    plugin.settings = {};

    var defaults = {
      selector: '.tk-sortable tbody',
      sortableOptions: {
        helper: function(e, ui) {
          return plugin.sortableHelper.call(this, e, ui);
        },
        stop: function (e, ui) {
          return plugin.sortableStop.call(this, e, ui);
        },
        start: function (e, ui) {
          return plugin.sortableStart.call(this, e, ui);
        }
      }
    };


    /**
     * plugin constructor
     */
    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);
      if (typeof $.fn.sortable === 'undefined') {
        if (typeof console !== 'undefined')
          console.error('Error: Sortable Jquery UI (http://jqueryui.com/) required for tableOrderBy plugin.');
        return;
      }
      $element.sortable(plugin.settings.sortableOptions).disableSelection();
    };

    // -- Private Methods --
    //var privateMethod = function(url) { };


    // -- Public Methods --
    //plugin.foo_public_method = function() { };

    plugin.sortableHelper = function(e, ui) {
      ui.children().each(function() {
        $(this).width($(this).width());
      });
      return ui;
    };
    plugin.sortableStop = function(e, ui) {
      //var url = ui.item.find('a').not('disabled').attr('href').split('?')[0];
      var url = ui.item.find('.tk-orderBy a').not('disabled').attr('href');
      var order = {};
      $element.find('tr').each(function (i) {
        order[i] = $(this).find('.tk-orderBy').data('objectid');
      });
      $.post(url, {newOrder: order}, function (data) {
        $element.empty().append($(data).find(plugin.settings.selector).find('tr'));
      } );
    };
    plugin.sortableStart = function(e, ui) { };

    // init the plugin
    plugin.init();
  };


  // Add the plugin to jQuery
  $.fn.tableOrderBy = function(options) {
    return this.each(function() {
      if (undefined == $(this).data('tableOrderBy')) {
        var plugin = new tableOrderBy(this, options);
        $(this).data('tableOrderBy', plugin);
      }
    });
  }
})(jQuery);



