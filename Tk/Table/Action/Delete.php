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
class Delete extends Iface
{

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string
     */
    protected $checkboxName = 'id';


    /**
     * Create
     *
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'delete', $checkboxName = 'id', $icon = 'glyphicon glyphicon-remove')
    {
        parent::__construct($name);
        $this->icon = $icon;
        $this->checkboxName = $checkboxName;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->makeInstanceKey($this->getName())]) || empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        /** @var \Tk\Db\Model $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (!$obj instanceof \Tk\Db\Model) continue;
            if (in_array($obj->getId(), $selected)) {
                $obj->delete();
                $i++;
            }
        }

        \Tk\Url::create()->delete($this->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        // TODO: Implement getHtml() method.
        $xhtml = <<<XHTML
<button type="submit" class="btn btn-xs disabled" title="Delete Selected Records." var="btn"><i var="icon" choice="icon"></i> </button>
XHTML;
        $template = \Dom\Loader::load($xhtml);

        if ($this->icon) {
            $template->addClass('icon', $this->icon);
            $template->setChoice('icon');
        }
        $template->appendHtml('btn', $this->getLabel());

        $btnId = $this->makeInstanceKey($this->getName());
        $template->setAttr('btn', 'id', 'fid-'.$btnId);
        $template->setAttr('btn', 'name', $btnId);
        $template->setAttr('btn', 'value', $btnId);


        // Element css class names
        foreach($this->getCssList() as $v) {
            $template->addClass('btn', $v);
        }

        $js = <<<JS
jQuery(function($) {

    var tid = '{$this->getTable()->getId()}';
    var cbName = '{$this->checkboxName}';
    var btnId = '$btnId';

    $('#fid-'+btnId).on('click', function (e) {
        var selected = $('#'+tid+' input[name^=\''+cbName+'\']:checked');
        if (!selected.length) return false;
        if (!confirm('Are you sure you want to delete the ' + selected.length + ' selected records?')) {
            return false;
        }
    });
    function initCb(e) {
        if (e && e.target.name == 'cb_'+cbName+'_all') {
            if ($(e.target).prop('checked')) {
                $('#fid-'+btnId).removeClass('disabled');
            } else {
                $('#fid-'+btnId).addClass('disabled');
            }
            return true;
        }
        if ($('#'+tid+' input[name^=\''+cbName+'\']:checked').length) {
            $('#fid-'+btnId).removeClass('disabled');
        } else {
            $('#fid-'+btnId).addClass('disabled');
        }
    }

    $('#'+tid+' input[name^=\''+cbName+'\'], #'+tid+' input[name^=\'cb_'+cbName+'_all\']').on('change', initCb);
    initCb();

});
JS;
        $template->appendJs($js);

        return $template;
    }


}
