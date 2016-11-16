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
     * @return Button
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
        if ($this->url instanceof \Tk\Uri)
            $this->url->redirect();
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $template = $this->getTemplate();
        if ($this->icon) {
            $template->addClass('icon', $this->icon);
            $template->setChoice('icon');
        }
        $template->appendHtml('btnTitle', $this->getLabel());

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        //$template->setAttr('btn', 'id', 'fid-'.$btnId);
        $template->setAttr('btn', 'id', $btnId);
        $template->setAttr('btn', 'name', $btnId);
        $template->setAttr('btn', 'value', $btnId);

        // Element css class names
        foreach($this->getCssList() as $v) {
            $template->addClass('btn', $v);
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
<button type="submit" class="btn btn-default btn-xs" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></button>
XHTML;
        return \Dom\Loader::load($xhtml);
    }


}
