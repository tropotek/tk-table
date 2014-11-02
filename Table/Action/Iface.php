<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * The interface for a table action
 *
 *
 * @package Table\Action
 */
abstract class Iface extends \Table\Element
{

    /**
     * @var string
     */
    protected $event = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string
     */
    protected $notes = '';

    /**
     * @var array
     */
    protected $class = array();

    /**
     * @var \Tk\Url
     */
    protected $url = null;


    /**
     * Create
     *
     * @param string $eventName
     * @param \Tk\Url $url
     * @param string $icon
     */
    public function __construct($eventName, $url = null, $icon = 'fa fa-cogs')
    {
        $this->event = $eventName;
        $this->label = ucfirst(preg_replace('/[A-Z]/', ' $0', $this->event));
        if (!$url) {
            $url = $this->getUri()->set($this->getObjectKey($this->event), $this->event);
        }
        $this->url = $url;
        $this->icon = $icon;
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\Template You can also return HTML string
     */
    public function getHtml($list)
    {
        return sprintf('<a class="" href="%s" title="%s"><span class="%s"></span>%s</a>',
            $this->url->toString(), $this->notes, $this->getClassString(), $this->text);
    }

    /**
     * Return the conncatenated class array
     *
     * @return string
     */
    protected function getClassString()
    {
        $class = '';
        if (count($this->class)) {
            $class = trim(implode(' ', $this->class));
        }
        return $class;
    }

    /**
     * Get the action label text
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label text of this action
     *
     * @param string $str
     * @return \Table\Action\Iface
     */
    public function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    /**
     * Get the notes text
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the notes of this action
     * This text will be uset as a tooltip or explanation of the action where aplicable
     *
     * @param string $str
     * @return \Table\Action\Iface
     */
    public function setNotes($str)
    {
        $this->notes = $str;
        return $this;
    }

    /**
     * Set the url
     *
     * @param \Tk\Url $url
     * @return \Table\Action\Iface
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the URL
     *
     * @return \Tk\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the cell event
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add a cell css class
     *
     * @param string $class
     * @return \Table\Action\Iface
     */
    public function addClass($class)
    {
        $this->class[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return \Table\Action\Iface
     */
    public function removeClass($class)
    {
        unset($this->class[$class]);
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    public function getClassList()
    {
        return $this->class;
    }

    /**
     * get Icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
