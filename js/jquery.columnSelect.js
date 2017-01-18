/**
 * Created by mifsudm on 18/01/17.
 */

if (typeof (String.prototype.hashCode) == 'undefined') {

  String.prototype.hashCode = function () {
    var hash = 0;
    if (this.length == 0) return hash;
    for (var i = 0; i < this.length; i++) {
       var char = this.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return Math.abs(hash);
  }

}

/**
 * This plugin template is from http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 *
 * jQuery Plugin Boilerplate
 * A boilerplate for jumpstarting jQuery plugins development
 * version 1.1, May 14th, 2011
 * by Stefan Gabos
 *
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').columnSelect({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('columnSelect').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('columnSelect').settings.foo;
 *   
 *   });
 * </code>
 */

// remember to change every instance of "columnSelect" to the name of your plugin!
(function($) {

  /**
   *
   * @param Element element
   * @param options
   */
  var columnSelect = function(element, options) {

    // plugin's default options
    // this is private property and is  accessible only from inside the plugin
    var defaults = {
      buttonId : '',
      selectors: null,
      hash: '',

      // Should return a jquery list containing checkbox inputs to trigger the columns.
      onInitColumnSelectors: function(topRow) {
        // by default the plugin will look for a button $('#buttonId') to append a list to..
        var button = $('#'+plugin.settings.buttonId);
        if (!button.length || !button.hasClass('btn')) {
          console.log('No button found!');
          return;
        }

        //button.detach();
        var btnGroup = $('<div class="btn-group"></div>');
        btnGroup.insertBefore(button);
        button.removeAttr('type');
        button.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
        button.append('<span class="caret"></span>');
        button.detach();
        btnGroup.append(button);
        // attach list ot it
        var ul = $('<ul class="dropdown-menu checkbox-menu columnSelect"></ul>');

        var tpl = $('<li><label for="" class="small"><input type="checkbox" id="" value="_fieldName"/> <span>Field Name</span></label></li>');
        topRow.each(function (i) {
          var row = tpl.clone();
          var label = 'Column ' + i;
          var rowId = 'column_'+i;
          if($(this).attr('data-label')) {
            label = $(this).attr('data-label');
          }
          row.find('input').attr('id', rowId);
          row.find('input').attr('name', 'columnSelect');
          row.find('input').attr('value', i);
          row.find('span').text(label);
          row.find('input').prop('checked', true);
          row.find('label').attr('for', row.find('input').attr('id'));

          ul.append(row);
        });

        btnGroup.append(ul);

        return ul.find('input');
      },

      // called when a user selects an item in the column list
      onChange: function () {
        console.log('onChange');

      },

      onSaveState: function () {
        console.log('onSaveState');
        var hasCookies = (typeof(Cookies) != 'undefined');
        if (!hasCookies) return;
        var json = JSON.stringify(plugin.settings.selectors.serializeArray());
        Cookies.set(plugin.settings.hash, json);
      },
      onRestoreState: function() {
        console.log('onRestoreState');
        var hasCookies = (typeof(Cookies) != 'undefined');
        if (!hasCookies) return;

//reset cookies
//Cookies.remove(plugin.settings.hash);

        var state = Cookies.get(plugin.settings.hash);

        // TODO: Get the default state of the columns (some may be hidden by default?)
        // How do we setup default hidden cols, maybe via the settings or a data attr in the headers?????
        var selected = plugin.range(0, plugin.settings.selectors.length-1);

        if (state) {
          selected = [];
          state = JSON.parse(state);
          $.each(state, function (i, o) {
            selected[selected.length] = o.value;
          });
        }

        plugin.settings.selectors.each(function(i) {
          if ($.inArray($(this).attr('data-cs-coll'), selected) != -1) {
            $(this).prop('checked', true).attr('data-cs-checked', 1);
          } else {
            $(this).prop('checked', false).attr('data-cs-checked', 0);
          }
        });

        refresh();
      }

    };

    // to avoid confusions, use "plugin" to reference the 
    // current instance of the object
    var plugin = this;

    var table = null;
    var topRow = null;

    // this will hold the merged default, and user-provided options
    // plugin's properties will be available through this object like:
    // plugin.settings.propertyName from inside the plugin or
    // element.data('columnSelect').settings.propertyName from outside the plugin, 
    // where "element" is the element the plugin is attached to;
    plugin.settings = {};

    var $element = $(element); // reference to the jQuery version of DOM element

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {

      // the plugin's final properties are the merged default and 
      // user-provided options (if any)
      plugin.settings = $.extend({}, defaults, options);


      // get the main table element in the block
      if (element.nodeName == 'TABLE') {
        table = $element;
      } else {
        table = $element.find('table').first();
      }
      if (!table) {
        console.error('jquery.columnSelect Error: No valid table found!');
        return;
      }

      if (plugin.settings.hash == '') {
        plugin.settings.hash = document.location.pathname.hashCode() + table.attr('id');
      }

      // Get the first row that we will use to setup the column selector
      topRow = table.find('th');
      if (!topRow || !topRow.length) {
        topRow = table.find('tr:nth-child(1) td');
      }
      if (!topRow.length) {
        console.error('jquery.columnSelect Error: No valid first row found.');
        return;
      }

      if (plugin.settings.onInitColumnSelectors != undefined) {
        plugin.settings.selectors = plugin.settings.onInitColumnSelectors.call(table, topRow);
      }

      if(!plugin.settings.selectors || !plugin.settings.selectors.length) {
        console.error('jquery.columnSelect Error: No valid column selectors found.');
        return;
      }

      plugin.settings.selectors.each(function(i) {
        $(this).attr('data-cs-coll', i);
      });
      plugin.settings.selectors.on('click', function (e) {

        var state = 0;
        if (!$(this).attr('data-cs-checked') || $(this).attr('data-cs-checked') == '' || $(this).attr('data-cs-checked') == '1') {
          state = 0;
        } else {
          state = 1;
        }
        $(this).attr('data-cs-checked', state);

        refresh();

        plugin.settings.onSaveState.call(this);
      });


      plugin.settings.onRestoreState.call(this);



    };

    // private methods
    // these methods can be called only from inside the plugin like:
    // methodName(arg1, arg2, ... argn)

    // a private method. for demonstration purposes only - remove it!
    var refresh = function() {

      // code goes here
      console.log('refresh');
      plugin.settings.selectors.each(function(i) {
        //$(this).attr('data-cs-coll', i);
        var nth = parseInt($(this).attr('data-cs-coll'))+1;
        var cells = table.find('tr th:nth-child('+nth+'), tr td:nth-child('+nth+')');

        if (!$(this).attr('data-cs-checked') || $(this).attr('data-cs-checked') == '' || $(this).attr('data-cs-checked') == '1') {
          cells.show();
        } else {
          cells.hide();
        }

      });

    };

    // public methods
    // these methods can be called like:
    // plugin.methodName(arg1, arg2, ... argn) from inside the plugin or
    // element.data('columnSelect').publicMethod(arg1, arg2, ... argn) from outside 
    // the plugin, where "element" is the element the plugin is attached to;

    // a public method. for demonstration purposes only - remove it!
    plugin.public_method = function() {

    };

    //
    plugin.range = function(start, end) {
      var foo = [];
      for (var i = start; i <= end; i++) {
        foo.push(i+'');
      }
      return foo;
    };

    // fire up the plugin!
    // call the "constructor" method
    plugin.init();

  };

  // add the plugin to the jQuery.fn object
  $.fn.columnSelect = function(options) {

    // iterate through the DOM elements we are attaching the plugin to
    return this.each(function() {

      // if plugin has not already been attached to the element
      if (undefined == $(this).data('columnSelect')) {

        // create a new instance of the plugin
        // pass the DOM element and the user-provided options as arguments
        var plugin = new columnSelect(this, options);

        // in the jQuery version of the element
        // store a reference to the plugin object
        // you can later access the plugin and its methods and properties like
        // element.data('columnSelect').publicMethod(arg1, arg2, ... argn) or
        // element.data('columnSelect').settings.propertyName
        $(this).data('columnSelect', plugin);

      }

    });

  }

})(jQuery);