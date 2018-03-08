<?php
namespace Tk\Table\Action;

use Tk\Table;

/**
 * The interface for a table Action
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    use \Tk\Dom\AttributesTrait;
    use \Tk\Dom\CssTrait;


    /**
     * This will be used for the event name using the instance ID
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var Table
     */
    protected $table = null;

    /**
     * @var boolean
     */
    protected $visible = true;

    
    /**
     * Create
     *
     * @param string $name The action event name
     */
    public function __construct($name)
    {
        $this->setName($name);
        $this->setLabel(ucfirst(preg_replace('/[A-Z]/', ' $0', $name)));
        $this->addCss('a'.ucFirst($name));
    }

    /**
     * Use this to init any code. This will be run on every page load.
     * 
     */
    public function init() {}

    /**
     * Execute the button event. This will only be called if the button name is in the request.
     * 
     * @return mixed
     */
    abstract public function execute();


    /**
     * @return \Dom\Template|string
     * @deprecated Use show()
     */
    public function getHtml() {
        return $this->show();
    }


    /**
     * Has this button action been fired
     *
     * @return bool
     */
    public function hasTriggered()
    {
        $request = $this->getTable()->getRequest();
        return !empty($request[$this->getTable()->makeInstanceKey($this->getName())]);
    }

    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the parent table object
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     * @throws \Tk\Exception
     */
    public function setName($name)
    {
        $name = preg_replace('/[^a-z0-9_-]/i', '_', $name);
        if (!preg_match('/[a-z0-9_-]+/i', $name)) {
            throw new \Tk\Exception('Invalid name value.');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the cell label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the cell label
     *
     * @param string $str
     * @return $this
     */
    public function setLabel($str)
    {
        $this->label = $str;
        return $this;
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

}
