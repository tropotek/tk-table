<?php
namespace Tk\Table\Cell;


use Tk\Ui\Element;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 * @todo Replace this with the \Tk\Ui\Button object
 */
class ActionButton extends Element
{

    /**
     * @var int
     */
    protected static $idx = 0;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var null|\Tk\Uri
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string
     */
    protected $css = '';

    /**
     * @var array
     */
    protected $attr = array();

    /**
     * @var boolean
     */
    protected $showLabel = false;

    /**
     * @var bool
     */
    protected $appendQuery = false;


    /**
     * @param string $title
     * @param null|\Tk\Uri|string $url
     * @param string $icon
     * @param string $css
     * @param array $attr
     * @param null|callable $onShow
     */
    public function __construct($title, $url = null, $icon = '', $css = '', $attr = array(), $onShow = null)
    {
        parent::__construct();
        $this->setTitle($title);
        if ($url)
            $this->setUrl($url);
        if ($icon)
            $this->setIcon($icon);
        $this->addCss($css);
        $this->setAttr($attr);
        $this->addOnShow($onShow);
    }

    /**
     * @param string $title
     * @param null|\Tk\Uri $url
     * @param string $icon
     * @param string $css
     * @return ActionButton
     */
    public static function create($title, $url = null, $icon = '', $css = '')
    {
        $obj = new self($title, $url, $icon, $css);
        return $obj;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return null|\Tk\Uri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null|\Tk\Uri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAppendQuery()
    {
        return $this->appendQuery;
    }

    /**
     * Should the url query parameters for the row URL be appended to this URL
     *
     * @param bool $appendQuery
     * @return $this
     */
    public function setAppendQuery($appendQuery = true)
    {
        $this->appendQuery = $appendQuery;
        return $this;
    }

}