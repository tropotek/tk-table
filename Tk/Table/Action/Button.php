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
     * @var string|\Tk\Url
     */
    protected $url = null;

    
    /**
     * Create
     *
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Url $url
     */
    public function __construct($name, $icon, $url = null)
    {
        parent::__construct($name);
        $this->icon = $icon;
        if ($url)
            $this->url = \Tk\Url::create($url);
    }

    /**
     * Create
     * 
     * @param string $name
     * @param string $icon
     * @param string|\Tk\Url $url
     * @return Button
     */
    static function getInstance($name, $icon, $url = null)
    {
        return new self($name, $icon, $url);
    }
    
    /**
     * @return mixed
     */
    public function execute()
    {
        if ($this->url instanceof \Tk\Url)
            $this->url->redirect();
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        // TODO: Implement getHtml() method.
        $xhtml = <<<XHTML
<button type="submit" class="btn btn-xs" var="btn"><i var="icon" choice="icon"></i> </button>
XHTML;
        $template = \Dom\Loader::load($xhtml);

        if ($this->icon) {
            $template->addClass('icon', $this->icon);
            $template->setChoice('icon');
        }
        $template->appendHtml('btn', $this->getLabel());

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $template->setAttr('btn', 'id', 'fid-'.$btnId);
        $template->setAttr('btn', 'name', $btnId);
        $template->setAttr('btn', 'value', $btnId);


        // Element css class names
        foreach($this->getCssList() as $v) {
            $template->addClass('btn', $v);
        }

        return $template;
    }


}
