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
     * ActionButton constructor.
     * @param string $title
     * @param null|\Tk\Url|string $url
     * @param string $icon
     * @param string $css
     * @param array $attr
     * @param null|callable $onShow
     */
    public function __construct($title, $url = null, $icon = '', $css = '', $attr = array(), $onShow = null)
    {
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
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
     */
    public function setOnShow($onShow)
    {
        $this->onShow = $onShow;
    }

    /**
     * @return bool
     */
    public function hasOnShow()
    {
        return is_callable($this->getOnShow());
    }

}