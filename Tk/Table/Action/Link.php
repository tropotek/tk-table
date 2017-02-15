<?php
namespace Tk\Table\Action;


/**
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Link extends Iface
{

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string|\Tk\Uri
     */
    protected $url = null;

    
    /**
     * Create
     *
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Uri $url
     */
    public function __construct($name, $icon, $url = null)
    {
        parent::__construct($name);
        $this->icon = $icon;
        if ($url)
            $this->url = \Tk\Uri::create($url);
    }

    /**
     * Create
     * 
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Uri $url
     * @return Link
     */
    static function create($name, $icon, $url = null)
    {
        return new static($name, $icon, $url);
    }
    
    /**
     * @return mixed
     */
    public function execute()
    {
        vd('No me thinks!!!!!');
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('id', $btnId);

        $template = $this->getTemplate();
        if ($this->icon) {
            $template->addCss('icon', $this->icon);
            $template->setChoice('icon');
        }
        $template->appendHtml('btnTitle', $this->getLabel());

        if ($this->url) {
            $template->setAttr('btn', 'href', $this->url);
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
     *
     * @return \Dom\Template
     */
    public function getTemplate()
    {
        $xhtml = <<<XHTML
<a class="btn btn-default btn-xs" href="javascript:;" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></a>
XHTML;
        return \Dom\Loader::load($xhtml);
    }


}
