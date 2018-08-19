<?php
namespace Tk\Table\Action;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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
     * @deprecated
     */
    protected $onExecute = null;

    /**
     * function (Delete $delete, $obj)
     * @var callable
     */
    protected $onDelete = null;

    /**
     * @var string
     */
    protected $confirmStr = 'Are you sure you want to delete the %selected% selected records?';


    /**
     * Create
     *
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     * @throws \Exception
     */
    public function __construct($name = 'delete', $checkboxName = 'id', $icon = 'fa fa-times')
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
     * @return static
     * @throws \Exception
     */
    static function create($name = 'delete', $checkboxName = 'id', $icon = 'fa fa-times')
    {
        return new static($name, $checkboxName, $icon);
    }

    /**
     * EG:  function (Delete $action, array $selected) { }
     *
     * @param callable $onExecute
     * @return $this
     * @deprecated
     */
    public function setOnExecute($onExecute)
    {
        \Tk\Log::warning('Deprecated function ');
        $this->onExecute = $onExecute;
        return $this;
    }

    /**
     * EG:  function (\Tk\Table\Action\Delete $action, $obj) { }
     *
     * @param callable $callable
     * @return $this
     */
    public function setOnDelete($callable)
    {
        $this->onDelete = $callable;
        return $this;
    }

    /**
     * @param $array
     * @return $this
     * @deprecated use setExcludeIdList()
     */
    public function setExcludeList($array)
    {
        return $this->setExcludeIdList($array);
    }

    /**
     * @param $array
     * @return $this
     */
    public function setExcludeIdList($array)
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
        $reqName = $this->getTable()->makeInstanceKey($this->getName());
        $request = $this->getTable()->getRequest();
        if (empty($request[$reqName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;

        // TODO: This is deprecated delete in the future
        $propagate = true;
        if (is_callable($this->onExecute)) {
            $p = call_user_func_array($this->onExecute, array($this, $selected));
            if ($p !== null && is_bool($p)) $propagate = $p;
        }

        if ($propagate) {
            /* @var \Tk\Db\Map\Model $obj */
            foreach($this->getTable()->getList() as $obj) {
                if (!is_object($obj)) continue;
                $keyValue = 0;
                if (property_exists($obj, $this->checkboxName)) {
                    $keyValue = $obj->{$this->checkboxName};
                }
                if (in_array($keyValue, $selected) && !in_array($keyValue, $this->getExcludeIdList())) {
                    $propagate = true;
                    if (is_callable($this->onDelete)) {
                        $p = call_user_func_array($this->onDelete, array($this, $obj));
                        if ($p !== null && is_bool($p)) $propagate = $p;
                    }
                    if ($propagate) {
                        $obj->delete();
                    }
                }
            }
        }

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {

        $this->setAttr('title', 'Delete Selected Records');
        $this->setAttr('disabled');
        $this->setAttr('data-cb-name', $this->checkboxName);
        $this->setAttr('data-cb-confirm', $this->getConfirmStr());

        $template = parent::show();

        $template->appendJs($this->getJs());
        return $template;
    }

    /**
     * @return string
     */
    protected function getConfirmStr()
    {
        return $this->confirmStr;
    }

    /**
     * @param string $confirmStr
     * @return Delete
     */
    public function setConfirmStr($confirmStr)
    {
        $this->confirmStr = $confirmStr;
        return $this;
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
      var confirmStr = btn.data('cb-confirm');
      
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
