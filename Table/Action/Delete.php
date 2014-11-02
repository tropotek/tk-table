<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * For this action to work the object must contain a delete() method
 *
 *
 * @package Table\Action
 */
class Delete extends Iface
{

    protected $confirmMsg = 'Are you sure you want to delete the selected records.';

    /**
     * Create a delete action
     *
     * @return \Table\Action\Delete
     */
    static function create()
    {
        $obj = new self('delete', \Tk\Request::getInstance()->getRequestUri(), 'fa fa-times');
        $obj->setLabel('Delete Selected');
        return $obj;
    }

    /**
     * setConfirm
     *
     * @param string $str
     * @return \Table\Action\Delete
     */
    public function setConfirm($str)
    {
        $this->confirmMsg = $str;
        return $this;
    }



    /**
     * (non-PHPdoc)
     * @see \Table\Action\Iface::execute()
     */
    public function execute($list)
    {
        $selected = $this->getRequest()->get($this->getObjectKey(\Table\Cell\Checkbox::CB_NAME));
        if (count($selected)) {
            $i = 0;
            foreach ($list as $obj) {
                if (!$obj instanceof \Tk\Db\Model) continue;
                if (in_array($obj->getId(), $selected)) {
                    $obj->delete();
                    $i++;
                }
            }
            $p = '';
            if ($i > 1) {
                $p = '`s';
            }
            \Mod\Notice::addSuccess('Record'.$p.' successfully deleted.');
        }

        $url = $this->getUri();
        $url->redirect();
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\Template You can also return HTML string
     */
    public function getHtml($list)
    {
        //$js = sprintf('submitForm(document.getElementById(\'%s\'), \'%s\');',
        $js = sprintf('tkFormSubmit(document.getElementById(\'%s\'), \'%s\');',
            $this->getTable()->getForm()->getId(), $this->getObjectKey($this->event));
        $js = sprintf("if(confirm('%s')) { %s } else { $(this).unbind('click'); }", $this->confirmMsg, $js);
        $ico = '';
        if ($this->getIcon()) {
            $ico = '<i class="'.$this->getIcon().'"></i> ';
        }
        return sprintf('<a class="%s btn btn-default btn-xs" href="javascript:;" onclick="%s" title="%s" onmousedown="$(window).unbind(\'beforeunload\');">%s%s</a>',
            $this->getClassString(), $js, $this->notes, $ico, $this->label);
    }


}
