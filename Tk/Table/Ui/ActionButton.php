<?php
namespace Tk\Table\Ui;

use Tk\Callback;
use Tk\Ui\Element;
use Tk\Ui\Link;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 * @todo Replace this with the \Tk\Ui\Button object
 */
class ActionButton extends Link
{

    /**
     * @var boolean
     */
    protected $showLabel = false;

    /**
     * @var bool
     */
    protected $appendQuery = false;


    /**
     * @param $text
     * @param null $url
     * @param string $icon
     * @param string $css
     * @return Link
     */
    public static function createBtn($text, $url = null, $icon = '', $css = 'btn btn-default btn-sm btn-xs')
    {
        $obj = self::create($text, $url, $icon);
        $obj->addCss($css);
        return $obj;
    }

    /**
     * function ($cell, $obj, $btn) {}
     *
     * @param callable $callable
     * @param int $priority [optional]
     * @return Element
     */
    public function addOnShow($callable, $priority=Callback::DEFAULT_PRIORITY)
    {
        $this->getOnShow()->append($callable, $priority);
        return $this;
    }


    /**
     * @return bool
     */
    public function isShowLabel()
    {
        return $this->showLabel;
    }

    /**
     * @param bool $showLabel
     * @return ActionButton
     */
    public function setShowLabel($showLabel = true)
    {
        $this->showLabel = $showLabel;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAppendQuery()
    {
        return ($this->hasAttr('data-append-query') && $this->getAttr('data-append-query') == 'true');
    }

    /**
     * Should the url query parameters for the row URL be appended to this URL
     *
     * @param bool $appendQuery
     * @return $this
     */
    public function setAppendQuery($appendQuery = true)
    {
        if ($this->isAppendQuery()) {
            $this->setAttr('data-append-query', 'true');
        } else {
            $this->removeAttr('data-append-query');
        }
        return $this;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $onShow = $this->getOnShow();
        //$this->setOnShow(null);     // Hide callable from parents
        $this->getOnShow()->setEnabled(false);
        if (!$this->isShowLabel()) $this->setText('');

        $template = parent::show();
        //$this->setOnShow($onShow);          // replace callable so Collection can call it during rendering
        $this->getOnShow()->setEnabled(true);
        return $template;
    }
}