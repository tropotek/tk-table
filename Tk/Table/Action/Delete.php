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
     * Create
     *
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'delete', $checkboxName = 'id', $icon = 'glyphicon glyphicon-remove')
    {
        parent::__construct($name, $icon);
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
     * @param $array
     * @return $this
     */
    public function setExcludeList($array)
    {
        $this->excludeIdList = $array;
        return $this;
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

        /* @var \Tk\Db\Map\Model $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (!$obj instanceof \Tk\Db\Map\Model) continue;
            // TODO: should we be using the checkboxName parameter to match against?????
            if (in_array($obj->getId(), $selected) && !in_array($obj->getId(), $this->excludeIdList)) {
                $obj->delete();
                $i++;
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

        $template = parent::getHtml();

        $template->appendJs($this->getJs());
        return $template;
    }

    /**
     * @return string
     */
    protected function getConfirmStr()
    {
        return "'Are you sure you want to delete the ' + selected.length + ' selected records?'";
    }

    /**
     * @return string
     */
    protected function getJs()
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $js = <<<JS
jQuery(function($) {
    var tid = '{$this->getTable()->getId()}';
    var cbName = '{$this->checkboxName}';
    var btnId = '#$btnId';
    
    $(btnId).on('click', function (e) {
        var selected = $('#'+tid+' input[name^=\''+cbName+'\']:checked');
        if (!selected.length) return false;
        if (!confirm({$this->getConfirmStr()})) {
            return false;
        }
    });
    
    function initCb(e) {
        if (e && e.target.name == cbName+'_all') {
            if ($(e.target).prop('checked')) {
                $(btnId).removeAttr('disabled');
            } else {
                $(btnId).attr('disabled', 'disabled');
            }
            return true;
        }
        if ($('#'+tid+' input[name^=\''+cbName+'\']:checked').length) {
            $(btnId).removeAttr('disabled');
        } else {
            $(btnId).attr('disabled', 'disabled');
        }
    }
    
    $('#'+tid+' input[name^=\''+cbName+'\'], #'+tid+' input[name^=\''+cbName+'_all\']').on('change', initCb);
    initCb();
});
JS;
        return $js;
    }

}
