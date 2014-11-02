<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Cell;

/**
 * The interface for a table Cell
 *
 *
 * @package Table\Cell
 */
abstract class Iface extends \Table\Element
{
    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';


    /**
     * This will be used for the cell header title
     * @var string
     */
    protected $label = '';

    /**
     * This is the row object's property name to access
     * it could be a getter starting with is, get, has or a public property
     * @var string
     */
    protected $property = '';

    /**
     * All classes appended to this tableData cell
     * @var array
     */
    protected $cellClass = array();

    /**
     * Any classes to append to this cell's parent <tr>
     * @var array
     */
    protected $rowClass = array();

    /**
     * This cell will contain the 'key' css class
     * @var bool
     */
    protected $key = false;

    /**
     * This cell's orderby property
     * By default this is set to use the cell property
     * if '' is used then ordering will be dissabled for this cell
     * @var bool
     */
    protected $orderProperty = null;

    /**
     * @var \Tk\Url
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $urlPropertyList = array();




    /**
     * Create
     *
     * @param string $property
     * @param string $label If null the property name is used EG: 'propName' = 'Prop Name'
     */
    public function __construct($property, $label = null)
    {
        $this->property = $property;
        if (!$label) {
            $label = ucfirst(preg_replace('/[A-Z]/', ' $0', $property));
        }
        $this->label = $label;
        $this->setOrderProperty($property);
    }

    /**
     * (non-PHPdoc)
     * @see Table_Element::execute()
     */
    public function execute($list) { }

    /**
     * Set the default cell data url
     *
     * @param \Tk\Url $url
     * @param array $urlPropertyList
     * @return \Table\Table
     */
    public function setUrl($url, $urlPropertyList = null)
    {
        $this->url = $url;
        if ($urlPropertyList) {
            $this->setUrlPropertyList($urlPropertyList);
        }
        return $this;
    }

    /**
     * Get the default data URL
     *
     * @return \Tk\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the url property list
     *
     * @param array $list
     * @return \Tk\Url
     */
    public function setUrlPropertyList($list)
    {
        if (!is_array($list)) {
            $list = array($list);
        }
        $this->urlPropertyList = $list;
        return $this;
    }

    /**
     * Set the property that the order header uses by default this is the same as property
     *
     * @param string $orderProperty
     * @return Iface
     */
    public function setOrderProperty($orderProperty)
    {
        $this->orderProperty = $orderProperty;
        return $this;
    }

    /**
     * Get the order by property name
     *
     * @return string
     */
    public function getOrderProperty()
    {
        return $this->orderProperty;
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
     * @return Iface
     */
    public function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    /**
     * Get the object property name to get data from
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Add a row css class
     *
     * @param string $class
     * @return Iface
     */
    public function addRowClass($class)
    {
        $this->rowClass[$class] = $class;
        return $this;
    }

    /**
     * remove a row css class
     *
     * @param string $class
     * @return Iface
     */
    public function removeRowClass($class)
    {
        unset($this->rowClass[$class]);
        return $this;
    }

    /**
     * reset and clear the row class
     *
     * @return Iface
     */
    public function clearRowClass()
    {
        $this->rowClass = array();
        return $this;
    }

    /**
     * Get the css row class list
     *
     * @return array
     */
    public function getRowClassList()
    {
        return $this->rowClass;
    }

    /**
     * Add a cell css class
     *
     * @param string $class
     * @return Iface
     */
    public function addCellClass($class)
    {
        $this->cellClass[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return Iface
     */
    public function removeCellClass($class)
    {
        unset($this->cellClass[$class]);
        return $this;
    }
    /**
     * reset and clear the class array
     *
     * @return Iface
     */
    public function clearCellClass()
    {
        $this->cellClass = array();
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    public function getCellClassList()
    {
        return $this->cellClass;
    }

    /**
     * Set the key cell property
     *
     * @param bool $b
     * @return Iface
     */
    public function setKey($b = true)
    {
        $this->key = ($b === true);
        return $this;
    }

    /**
     * Is this cell a key cell
     * @return bool
     */
    public function isKey()
    {
        return ($this->key === true);
    }

    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering scripts.
     *
     * @param stdClass $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        if (!$this->property || !$obj) {
            return $obj;
        }
        if (is_array($obj) && isset($obj[$this->property])) {
            return $obj[$this->property];
        }
        // Try to return property
        if (property_exists($obj, $this->property)) {
            return $obj->{$this->property};
        }
        // Get property by method if accessor exists
        $method = 'get' . ucfirst($this->property);
        if (!method_exists($obj, $method)) {
            $method = 'is' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            $method = 'has' . ucfirst($this->property);
        }
        if (!method_exists($obj, $method)) {
            $method = '';
        }
        if ($method) {
            return $obj->$method();
        }
        return $obj;
    }

    /**
     * Returns a property value ready to insert into a csv file
     * Override this for types that you want to render differently in csv
     *
     * @param stdClass $obj
     * @return string
     */
    public function getCsv($obj)
    {
        return strip_tags($this->getPropertyValue($obj));
    }

    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * This is the HTML rendered output of the data.
     *
     * @param \Tk\Object $obj
     * @return \Dom\Template Alternativly you can return a plain HTML string
     */
    public function getTd($obj)
    {
        $this->rowClass = array(); // reset row class list
        $str = '';

        $url = $this->getUrl();
        if ($url) {
            if (count($this->urlPropertyList)) {
                $tmpP = $this->property;
                foreach ($this->urlPropertyList as $prop) {
                    $this->property = $prop;
                    $url->set($prop, $this->getPropertyValue($obj));
                }
                $this->property = $tmpP;
            } else {
                $class = get_class($obj);
                $pos = strrpos($class, '\\');
                if (!$pos === false) {
                    $name = substr(get_class($obj), $pos + 1);
                } else {
                    $name = $class;
                }
                $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
                $url->set($prop, $obj->id);
            }
            $str = '<a href="' . htmlentities($url->toString()) . '">' . htmlentities($this->getPropertyValue($obj)) . '</a>';
        } else {
            $str = htmlentities($this->getPropertyValue($obj));
        }
        return $str;
    }

    /**
     * Get the table data from an object if available
     *   Overide getTh() to add new text to the header.
     *
     * This is the HTML rendered output of the header title.
     *
     * @return \Dom\Template Alternativly you can return a plain HTML string
     */
    public function getTh()
    {
        $url = $this->getOrderUrl();
        if ($url) {
            $str = '<a href="' . htmlentities($url->toString()) . '" class="noBlock" title="Click to order by: ' . $url->get($this->getObjectKey('orderBy')) . '">' . $this->getLabel() . '</a>';
        } else {
            $str = '<span>' . $this->getLabel() . '</span>';
        }
        return $str;
    }

    /**
     * getOrderByUrl
     *
     * @return \Tk\Url
     */
    public function getOrderUrl()
    {
        if (!$this->getOrderProperty()) {
            return;
        }
        //$pre = '`' . $this->getOrderProperty() . '` ';
        $pre = $this->getOrderProperty() . ' ';
        $eventKey = $this->getObjectKey('orderBy');
        $url = $this->getUri();
        $url->delete($eventKey);

        $order = $this->getOrder();
        if ($order == self::ORDER_ASC) {
            $url->set($eventKey, $pre . self::ORDER_DESC);
        } else if ($order == self::ORDER_DESC) {
            $url->set($eventKey, self::ORDER_NONE);
        } else if ($order == self::ORDER_NONE) {
            $url->set($eventKey, $pre . self::ORDER_ASC);
        }
        return $url;
    }


    /**
     * Get the order status of this cell
     *
     * @return string
     * @note: This does not take into account multiple orders EG: `id` DESC, `field2` ASC, etc
     *   Only the first one will be compared
     */
    public function getOrder()
    {
        $pre = $this->getOrderProperty();
        $orderByStr = '';
        if ($this->getTable()->getDbTool()) {
            $orderByStr = $this->getTable()->getDbTool()->getOrderBy();
        }
        if (preg_match('/^(`)?' . $pre . '(`)? ' . self::ORDER_DESC . '/i', $orderByStr)) {
            return self::ORDER_DESC;
        } else if (preg_match('/^(`)?' . $pre . '(`)?( ' . self::ORDER_ASC . ')?/i', $orderByStr)) {
            return self::ORDER_ASC;
        }
        return self::ORDER_NONE;
    }

}
