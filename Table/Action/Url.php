<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * This is a base url action object, useful for adding link only actions to a Table.
 *
 *
 * @package Table\Action
 */
class Url extends Iface
{

    protected $msg = '';

    /**
     * Create a delete action
     *
     * @param string $label
     * @param \Tk\Url $url
     * @param string $class
     * @param string $confirmMsg
     * @return \Table\Action\Url
     */
    static function create($label, $url, $class = '', $confirmMsg = '')
    {
        $obj = new self('none', $url, 'fa fa-link');
        if ($class) {
            $obj->addClass($class);
        }
        $obj->setLabel($label);
        $obj->msg = $confirmMsg;
        return $obj;
    }

    /**
     * (non-PHPdoc)
     * @see Iface::execute()
     */
    public function execute($list) { }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\Template You can also return HTML string
     */
    public function getHtml($list)
    {
        $onclick = '';
        if ($this->msg) {
            $onclick = "onclick=\"return confirm('{$this->msg}');\"";
        }
        return sprintf('<a class="btn btn-defult btn-xs" href="%s" title="%s" %s><span class="%s"></span>%s</a>',
            htmlentities($this->url->toString()), $this->notes, $onclick, $this->getClassString(), $this->label);
    }

}