<?php
namespace Tk\Table\Action;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @deprecated use \Tk\Ui\Dialog\Dialog
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
     * @var string
     */
    protected $checkboxName = 'id';


    /**
     * @param string $name
     * @param string $icon
     * @deprecated use \Tk\Ui\Dialog\Dialog
     */
    public function __construct($name = 'dialog', $icon = 'fa fa-file-o')
    {
        parent::__construct($name, $icon);
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-dialog');
    }

    /**
     * @param string $name
     * @return Dialog
     * @deprecated use \Tk\Ui\Dialog object lib
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
    public function getCheckboxName()
    {
        return $this->checkboxName;
    }

    /**
     * @param string $checkboxName
     */
    public function setCheckboxName($checkboxName)
    {
        $this->checkboxName = $checkboxName;
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
    public function show()
    {
        $template = $this->getTemplate();
        $dialogId = $this->getDialogId();

        $this->setAttr('data-target', '#'.$dialogId);
        $this->setAttr('data-cb-name', $this->getCheckboxName());
        $template = parent::show();

        $template->setAttr('dialog', 'id', $dialogId);
        $template->setAttr('dialog', 'aria-labelledby', $dialogId.'Label');
        $template->setAttr('title', 'id', $dialogId.'Label');
        $template->insertText('title', $this->getLabel());


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
    
    
    $('.tk-action-dialog').each(function () {
      var btn = $(this).find('.btn-action');
      var cbName = btn.data('cb-name');
      
      // btn.on('click', function () {
      //   var selected = $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked');
      //   return selected.length > 0 && confirm(confirmStr.replace(/%selected%/, selected.length));
      // });
      
      btn.closest('.tk-table').on('change', '.table-body input[name^="'+cbName+'"]', function () { updateBtn(btn); });
      updateBtn(btn);
    });
});
JS;
        $template->appendJs($js);


        if ($this->getOnShow()) {
            call_user_func_array($this->getOnShow(), array($this));
        }
        return $template;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div style="display: inline-block;" class="tk-action-dialog">
  
  <button class="btn btn-default btn-sm btn-action" data-toggle="modal" data-target="#myModal" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></button>
  
  <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="myModal" var="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel" var="title">Modal title</h4>
        </div>
        <div class="modal-body" var="body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-close" data-dismiss="modal" var="close">Close</button>
          <button type="button" class="btn btn-primary btn-submit" var="submit">Submit</button>
        </div>
      </div>
    </div>
  </div>
  
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}
