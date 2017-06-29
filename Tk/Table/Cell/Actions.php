<?php
namespace Tk\Table\Cell;

/**
 * TODO: Move to \Tk\Ui\Table\Cell
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Actions extends Text
{
    /**
     * @var \Tk\Collection
     */
    protected $buttonList = null;

    /**
     * Actions constructor.
     * @param string $property
     * @param null|string $label
     */
    public function __construct($property='actions', $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
        $this->setVisible(false);
        $this->buttonList = new \Tk\Collection();
    }

    /**
     * @param ActionButton $button
     * @return ActionButton
     */
    public function addButton($button) {
        $this->setVisible(true);
        $this->buttonList->set($button->getId(), $button);
        return $button;
    }

    /**
     * @param ActionButton $srcButton
     * @param ActionButton $button
     * @return ActionButton
     */
    public function addButtonBefore($srcButton, $button)
    {
        $newArr = array();
        if (!count($this->buttonList)) {
            $this->addButton($button);
            return $button;
        }
        foreach ($this->buttonList as $k => $v) {
            if ($k == $srcButton->getId()) {
                $newArr[$button->getId()] =  $button;
            }
            $newArr[$k] = $v;
        }
        $this->buttonList->clear()->replace($newArr);
        return $button;
    }

    /**
     * @param ActionButton $srcButton
     * @param ActionButton $button
     * @return ActionButton
     */
    public function addButtonAfter($srcButton, $button)
    {
        $newArr = array();
        if (!count($this->buttonList)) {
            $this->addButton($button);
            return $button;
        }
        foreach ($this->buttonList as $k => $v) {
            $newArr[$k] = $v;
            if ($k == $srcButton->getId()) {
                $newArr[$button->getId()] =  $button;
            }
        }
        $this->buttonList->clear()->replace($newArr);
        return $button;
    }

    /**
     * @param int $id
     * @return null|ActionButton
     */
    public function findButton($id)
    {
        if ($this->buttonList->has($id))
            return $this->buttonList->get($id);
    }

    /**
     * @param int|ActionButton $id
     * @return null|ActionButton Return null if no button removed
     */
    public function removeButton($id)
    {
        if ($id instanceof ActionButton) $id = $id->getId();

        if (!$this->buttonList->has($id)) return null;
        $button = $this->buttonList->get($id);
        $this->buttonList->remove($id);
        return $button;
    }

    public function getRawValue($obj)
    {
        $arr = array();
        /** @var ActionButton $btn */
        foreach ($this->buttonList as $btn) {
            $arr[] = $btn->getTitle();
        }
        return '['.implode('][', $arr).']';
    }

    /**
     * @param \App\Db\User $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $template = $this->__makeTemplate();
        /** @var ActionButton $srcBtn */
        foreach ($this->buttonList as $srcBtn) {
            $btn = clone $srcBtn;
            if ($btn->hasOnShow()) {
                call_user_func_array($btn->getOnShow(), array($obj, $btn, $this));
            }
            if (!$btn->isVisible()) continue;
            $row = $template->getRepeat('btn');

            if ($btn->getUrl()) {
                $row->setAttr('btn', 'href', $btn->getUrl());
            } else {
                $row->setAttr('btn', 'href', '#');
            }
            $row->setAttr('btn', 'title', $btn->getTitle());
            $css = $btn->getCssString();
            if (!$css) {
                $css = 'btn-default';
            }
            $row->addCss('btn', $css);
            if ($btn->getIcon()) {
                $row->addCss('icon', $btn->getIcon());
            } else if ($btn->getTitle()) {
                $row->insertText('icon', $btn->getTitle());
            }
            if (count($btn->getAttrList())) {
                $row->setAttr('btn', $btn->getAttrList());
            }
            $row->appendRepeat();
        }
        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="tk-table-actions">
  <a href="#" class="btn btn-xs btn-default" title="" var="btn" repeat="btn"><i var="icon" class=""></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
    
}