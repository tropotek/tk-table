/**
 * Tk table javascript
 */

jQuery(function ($) {

  tkRegisterInit(function () {
    // run initTable for each table in this element
    $('.tk-table', this).each(function() { initTable.call(this); });
  });

  function initTable() {
    let tkTable = $(this);

    // Class: \Tk\Table
    // Table limit on-change event
    $('.tk-limit select', tkTable).change(function (e) {
      if ($(this).val() == 0 && $(this).data('total') > 1000) {
        if (!confirm('WARNING: There are large number of records, page load time may be slowed.')) return false;
      }
      const searchParams = new URLSearchParams(location.search);
      searchParams.set($(this).data('name'), $(this).val());
      searchParams.delete($(this).data('page'));
      location.search = searchParams.toString();
      return false;
    });


    // Class: \Tk\Table\Cell\RowSelect
    $('.tk-tcb-head', tkTable).on('change', function(e) {
      let cbh = $(this);
      let name = cbh.attr('name').match(/([a-zA-Z0-9]+)_all/i)[1];
      let list = $(`input[name^="${name}"]`, tkTable);
      list.prop('checked', cbh.prop('checked'));
    }).trigger('change');


    // Class: \Tk\Table\Action\Select
    function updateBtn(btn) {
      if (!btn.data('selectedOnly')) return;
      var rsName = btn.data('rowSelect');
      btn.prop('disabled', false);
      if(!$(`input[name^="${rsName}"]:checked`, tkTable).length) {
        btn.prop('disabled', true);
      }
    }
    $('.tk-action-select', tkTable).each(function () {
      var btn = $(this);
      var rsName = btn.data('rowSelect');
      btn.data('selectedOnly', btn.prop('disabled'))
      btn.on('click', function () {
        return $(`input[name^="${rsName}"]:checked`, tkTable).length > 0;
      });
      btn.closest('.tk-table').on('change', `input[name^="${rsName}"]`, function () {
        updateBtn(btn);
      });
      updateBtn(btn);
    });

    // Class: \Tk\Table\Cell\OrderBy
    $('.tk-sortable', tkTable).each(function() {
      if ($.fn.tableOrderBy === undefined) {
        console.warn('jQuery plugin tableOrderBy not found.');
      }
      $('tbody', this).tableOrderBy({
        selector: '.tk-sortable tbody',
        handle: '.tk-orderBy .drag',
      });
    });

  };

});

/**
 * This plugin for class \Tk\Tabl\Cell\OrderBy
 */
(function($) {
  var tableOrderBy = function(element, options) {
    // Current instance of the object
    var plugin = this;
    // reference to the jQuery version of DOM element
    var $element = $(element);
    // this plugins current settings
    plugin.settings = {};

    var defaults = {
      selector: '.tk-sortable tbody',
      handle: '',
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
      $element.sortable($.extend({}, plugin.settings.sortableOptions, {handle: plugin.settings.handle})).disableSelection();
    };

    plugin.sortableHelper = function(e, ui) {
      ui.children().each(function() {
        $(this).width($(this).width());
      });
      return ui;
    };

    plugin.sortableStop = function(e, ui) {
      var url = ui.item.find('.tk-orderBy .btn-group a').not('.disabled').attr('href');
      var order = {};

      $element.find('tr').each(function (i) {
        order[i] = $(this).find('.tk-orderBy').data('orderbyId');
      });

      $.post(url, {newOrder: order}, function (data) {
        $element.empty().append($(data).find(plugin.settings.selector).find('tr'));
      } );
    };

    plugin.sortableStart = function(e, ui) { };

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
