<?php
namespace Tk\Table\Action;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SelectAll extends Button
{

    /**
     * @var string
     */
    protected $checkboxName = 'id';


    /**
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'selectAll', $checkboxName = 'id', $icon = 'fa fa-square-o')
    {
        parent::__construct($name, $icon);
        $this->checkboxName = $checkboxName;
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-select-all');
    }

    /**
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return SelectAll
     */
    static function create($name = 'selectAll', $checkboxName = 'id', $icon = 'fa fa-square-o')
    {
        return new static($name, $checkboxName, $icon);
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {
        $this->setAttr('title', 'Select All/None');
        $this->setAttr('data-cb-name', $this->checkboxName);
        $this->addCss('tk-action-select-all');

        $template = parent::show();

        $js = <<<JS
jQuery(function($) {
  $('.tk-action-select-all').on('click', function () {
    var cbName = $(this).data('cb-name');
    if ($(this).hasClass('selected')) {
      $(this).removeClass('selected');
      $(this).find('i').attr('class', 'fa fa-square-o');
      $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]').prop('checked', false).trigger('change');
    } else {
      $(this).addClass('selected');
      $(this).find('i').attr('class', 'fa fa-check-square-o');
      $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]').prop('checked', true).trigger('change');
    }
  });
});
JS;
        $template->appendJs($js);

        return $template;
    }

}
