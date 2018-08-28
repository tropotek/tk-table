<?php
namespace Tk\Table\Action;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Link extends Iface
{

    /**
     * @var string
     */
    protected $icon = null;

    /**
     * @var \Tk\Uri
     */
    protected $url = null;


    /**
     * @param string $name
     * @param string|\Tk\Uri|null $url
     * @param string|null $icon
     */
    public function __construct($name, $url = null, $icon = null)
    {
        parent::__construct($name);
        if ($url)
            $this->setUrl($url);
        if ($icon)
            $this->setIcon($icon);
        $this->addCss('btn btn-default btn-sm btn-xs');
    }

    /**
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Uri|null $url
     * @return Link
     * @todo: we need to re-arrange these params, use createLink for now we will fix this in another major version
     * @deprecated use createLink($name, $url, $icon)
     */
    static function create($name, $icon, $url = null)
    {
        return new static($name, $url, $icon);
    }

    /**
     * @param string $name
     * @param string|\Tk\Uri|null $url
     * @param string|null $icon
     * @return static
     * @since 2.0.68
     */
    static function createLink($name, $url = null, $icon = null)
    {
        return new static($name, $url, $icon);
    }
    
    /**
     *
     */
    public function execute() { }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('id', $btnId);

        $template = $this->getTemplate();
        if ($this->getIcon()) {
            $template->addCss('icon', $this->getIcon());
        } else {
            $template->hide('icon');
        }
        $template->appendHtml('btnTitle', $this->getLabel());

        if ($this->getUrl()) {
            $template->setAttr('btn', 'href', $this->getUrl());
        }

        // Add class names
        foreach($this->getCssList() as $v) {
            $template->addCss('btn', $v);
        }

        // Add new attribute values
        foreach($this->getAttrList() as $k => $v) {
            $template->setAttr('btn', $k, $v);
        }

        return $template;
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
     * @return \Tk\Uri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \Tk\Uri|string $url
     * @return Link
     */
    public function setUrl($url)
    {
        $this->url = \Tk\Uri::create($url);
        return $this;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<a class="" href="javascript:;" var="btn"><i var="icon"></i> <span var="btnTitle"></span></a>
XHTML;
        return \Dom\Loader::load($xhtml);
    }


}
