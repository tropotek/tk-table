<?php
namespace Tk\Table\Ui;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 * @todo Replace this with the \Tk\Ui\Button object
 */
class ActionButton extends \Tk\Ui\Link
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
     * @note this is a helper class to remove the Z
     */
    public static function createBtn($text, $url = null, $icon = '', $css = 'btn btn-default btn-sm btn-xs')
    {
        $obj = self::create($text, $url, $icon);
        $obj->addCss($css);
        return $obj;
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
        $this->setOnShow(null);     // Hide callable from parents
        if (!$this->isShowLabel()) $this->setText('');

        $template = parent::show();
        $this->setOnShow($onShow);          // replace callable so Collection can call it during rendering
        return $template;
    }
}