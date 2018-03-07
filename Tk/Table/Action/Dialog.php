<?php
namespace Tk\Table\Action;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dialog extends Button
{

    /**
     * @var null|callable
     */
    public $onShow = null;

    /**
     * @var null|callable
     */
    public $onExecute = null;


    /**
     * @param string $name
     */
    public function __construct($name = 'dialog', $icon = 'fa fa-file-o')
    {
        parent::__construct($name, $icon);
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-dialog');
    }

    /**
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return Dialog
     */
    static function create($name = 'dialog')
    {
        return new static($name);
    }


    /**
     * @return callable|null
     */
    public function getOnShow()
    {
        return $this->onShow;
    }

    /**
     * Eg: function ($dialog) { }
     *
     * @param callable|null $onShow
     * @return $this
     */
    public function setOnShow($onShow)
    {
        $this->onShow = $onShow;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getOnExecute()
    {
        return $this->onExecute;
    }

    /**
     * Eg: function ($dialog) { }
     *
     * @param callable|null $onExecute
     * @return $this
     */
    public function setOnExecute($onExecute)
    {
        $this->onExecute = $onExecute;
        return $this;
    }

    /**
     * @return string
     */
    public function getDialogId()
    {
        return 'id-' . $this->getName();
    }


    /**
     * @return mixed|void
     */
    public function execute()
    {

        if ($this->getOnExecute()) {
            call_user_func_array($this->getOnExecute(), array($this));
        }

    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $template = parent::getHtml();
        $dialogId = $this->getDialogId();

        $template->setAttr('btn', 'data-target', '#'.$dialogId);
        $template->setAttr('dialog', 'id', $dialogId);
        $template->setAttr('dialog', 'aria-labelledby', $dialogId.'Label');
        $template->setAttr('title', 'id', $dialogId.'Label');
        $template->insertText('title', $this->getLabel());

        if ($this->getOnShow()) {
            call_user_func_array($this->getOnShow(), array($this));
        }

        return $template;
    }

    /**
     * @return \Dom\Template
     */
    public function getTemplate()
    {
        $xhtml = <<<XHTML
<div style="display: inline-block;" class="tk-action-dialog">
  
  <button class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></button>
  
  <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="myModal" var="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel" var="title">Modal title</h4>
        </div>
        <div class="modal-body" var="body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" var="close">Close</button>
          <button type="button" class="btn btn-primary dialog-submit" var="submit">Submit</button>
        </div>
      </div>
    </div>
  </div>
  
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}
