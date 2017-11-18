<?php
namespace Tk\Table\Action;


/**
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Delete extends Button
{

    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * @var array
     */
    protected $excludeIdList = array();

    /**
     * function (Delete $delete, array $selected)
     * @var callable
     */
    protected $onExecute = null;


    /**
     * Create
     *
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'delete', $checkboxName = 'id', $icon = 'glyphicon glyphicon-remove')
    {
        parent::__construct($name, $icon);
        $this->addCss('tk-action-delete');
        $this->checkboxName = $checkboxName;
    }

    /**
     * Create
     * 
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return Delete
     */
    static function create($name = 'delete', $checkboxName = 'id', $icon = 'glyphicon glyphicon-remove')
    {
        return new static($name, $checkboxName, $icon);
    }

    /**
     * @param callable $onExecute
     * @return $this
     */
    public function setOnExecute($onExecute)
    {
        $this->onExecute = $onExecute;
        return $this;
    }

    /**
     * @param $array
     * @return $this
     */
    public function setExcludeList($array)
    {
        $this->excludeIdList = $array;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeIdList()
    {
        return $this->excludeIdList;
    }


    /**
     * @return mixed
     */
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        $propagate = true;
        if (is_callable($this->onExecute)) {
            $p = call_user_func_array($this->onExecute, array($this, $selected));
            if ($p !== null && is_bool($p)) $propagate = $p;
        }
        if ($propagate) {
            /* @var \Tk\Db\Map\Model $obj */
            foreach($this->getTable()->getList() as $obj) {
                if (!$obj instanceof \Tk\Db\Map\Model) continue;
                // TODO: should we be using the checkboxName parameter to match against?????
                if (in_array($obj->getId(), $selected) && !in_array($obj->getId(), $this->excludeIdList)) {
                    $obj->delete();
                    $i++;
                }
            }
        }

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {

        $this->setAttr('title', 'Delete Selected Records');
        $this->setAttr('disabled');
        $this->setAttr('data-cb-name', $this->checkboxName);
        $this->setAttr('data-confirm', $this->getConfirmStr());

        $template = parent::getHtml();

        $template->appendJs($this->getJs());
        return $template;
    }

    /**
     * @return string
     */
    protected function getConfirmStr()
    {
        return "'Are you sure you want to delete the %selected% selected records?'";
    }

    /**
     * @return string
     */
    protected function getJs()
    {
        $js = <<<JS
jQuery(function($) {
    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      if(btn.closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked').length) {
        btn.removeAttr('disabled');
      } else {
        btn.attr('disabled', 'disabled');
      }
    }
    
    $('.tk-action-delete').each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
      var confirmStr = btn.data('confirm');
      
      btn.on('click', function () {
        var selected = $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked');
        return selected.length > 0 && confirm(confirmStr.replace(/%selected%/, selected.length));
      });
      btn.closest('.tk-table').on('change', '.table-body input[name^="'+cbName+'"]', function () { updateBtn(btn); });
      
      updateBtn(btn);
    });
});
JS;
        return $js;
    }

}
