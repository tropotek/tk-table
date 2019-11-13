<?php
namespace Tk\Table\Cell;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ButtonCollection extends Text
{
    use \Tk\Ui\ElementCollectionTrait;


    /**
     * Actions constructor.
     * @param string $property
     * @param null|string $label
     */
    public function __construct($property = 'actions', $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
        //$this->setVisible(false);
    }

    /**
     * Used for things like CSV export
     *
     * @param mixed $obj
     * @return string
     */
    public function getRawValue($obj)
    {
        $arr = array();
        /** @var \Tk\Table\Ui\ActionButton $btn */
        foreach ($this->getElementList() as $btn) {
            if ($btn->isVisible())
                $arr[] = $btn->getText();
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
        if (!$this->isVisible()) {
            return $template;
        }
        $groups = array();
        /** @var \Tk\Table\Ui\ActionButton $srcBtn */
        foreach ($this->getElementList() as $srcBtn) {
            $btn = clone $srcBtn;
            if ($btn->hasOnShow()) {
                call_user_func_array($btn->getOnShow(), array($this, $obj, $btn));
            }
            if (!$btn->isVisible()) continue;
            $url = $btn->getUrl();
            if ($url) {
                if ($btn->getAttr('data-append-query') == 'true') {
                    $urlProperty = $this->getUrlProperty() ? $this->getUrlProperty() : 'id';
                    list($prop, $val) = $this->getRowPropVal($obj, $urlProperty);
                    $url = clone $url->set($prop, $val);
                }
                $btn->setUrl($url);
            }
            if (!$btn->getGroup()) {
                $groups[$btn->getGroup()][] = $btn->show();
            } else {
                if(!isset($groups[$btn->getGroup()])) {
                    $groups[$btn->getGroup()] = \Dom\Loader::load('<div class="btn-group" role="group" var="group"></div>');
                }
                $groups[$btn->getGroup()]->appendTemplate('group', $btn->show());
            }
        }

        foreach ($groups as $group => $tpl) {
            if (is_array($tpl)) {
                foreach ($tpl as $t) $template->appendTemplate('body', $t);
            } else {
                $template->appendTemplate('body', $tpl);
            }
        }

        $template->addCss('body', $this->getCssList());
        $template->setAttr('body', $this->getAttrList());

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
<div class="tk-table-actions btn-toolbar" role="toolbar" var="body"></div>
HTML;
        return \Dom\Loader::load($html);
    }
    
}