<?php
namespace Tk\Table\Action;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Button extends Iface
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
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Uri $url
     * @throws \Tk\Exception
     */
    public function __construct($name, $icon, $url = null)
    {
        parent::__construct($name);
        $this->icon = $icon;
        if ($url)
            $this->url = \Tk\Uri::create($url);
        $this->setAttr('type', 'submit');
    }

    /**
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Uri $url
     * @return Button
     * @throws \Tk\Exception
     */
    static function createButton($name, $icon, $url = null)
    {
        return new static($name, $icon, $url);
    }
    
    /**
     * @return mixed
     */
    public function execute()
    {
        if ($this->url instanceof \Tk\Uri)
            $this->url->redirect();
    }

    /**
     * @return string|\Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = $this->getTemplate();

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('id', $btnId);
        $this->setAttr('name', $btnId);
        $this->setAttr('value', $btnId);

        if ($this->icon) {
            $template->addCss('icon', $this->icon);
            $template->setChoice('icon');
        }
        $template->appendHtml('btnTitle', $this->getLabel());

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
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<button class="btn btn-default btn-sm" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></button>
XHTML;
        return \Dom\Loader::load($xhtml);
    }


}
