<?php
namespace Tk\Table\Cell;


/**
 * Class ActionButton
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class ActionButton
{
    use \Tk\Dom\AttributesTrait;
    use \Tk\Dom\CssTrait;

    /**
     * @var int
     */
    protected static $idx = 0;

    /**
     * @var int
     */
    protected $id = 0;

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
     * @var null|callable
     */
    protected $onShow = null;

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var bool
     */
    protected $appendQuery = false;


    /**
     * ActionButton constructor.
     * @param string $title
     * @param null|\Tk\Uri|string $url
     * @param string $icon
     * @param string $css
     * @param array $attr
     * @param null|callable $onShow
     */
    public function __construct($title, $url = null, $icon = '', $css = '', $attr = array(), $onShow = null)
    {
        $this->id = self::$idx++;
        $this->title = $title;
        $this->url = $url;
        $this->icon = $icon;
        $this->addCss($css);
        $this->setAttr($attr);
        $this->onShow = $onShow;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * @return callable|null
     */
    public function getOnShow()
    {
        return $this->onShow;
    }

    /**
     * @param callable|null $onShow
     * @return $this
     */
    public function setOnShow($onShow)
    {
        $this->onShow = $onShow;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOnShow()
    {
        return is_callable($this->getOnShow());
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
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