<?php
namespace Tk\Table\Cell;

use Tk\Table;
use Tk\Uri;

/**
 * The interface for a table Cell
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface
{

    use \Tk\Dom\AttributesTrait;
    use \Tk\Dom\CssTrait;


    /**
     * @var \Tk\Table\Row
     */
    protected $row = null;
    
    /**
     * This will be used for the cell header title
     * @var string
     */
    protected $label = '';

    /**
     * @var bool
     */
    protected $showLabel = false;

    /**
     * This is the row object's property name
     * @var string
     */
    protected $property = '';

    /**
     * This cell's order property
     * By default this is set to use the cell property
     * if '' is used then ordering will be disabled for this cell
     * @var bool
     */
    protected $orderProperty = null;

    /**
     * @var string|Uri
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $urlProperty = '';

    /**
     * @var Table
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $store = array();

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var null|callable
     */
    protected $onPropertyValue = null;

    /**
     * @var null|callable
     */
    protected $onCellHtml = null;

    
    

    /**
     * Create
     *
     * @param string $property
     * @param null|string $label If null the property name is used EG: 'propName' = 'Prop Name'
     */
    public function __construct($property, $label = null)
    {
        $this->property = $property;
        if (!$label) {
            $label = preg_replace('/Id$/', '', $property);
            $label = ucfirst(preg_replace('/[A-Z]/', ' $0', $label));
        }
        $this->label = $label;
        $this->row = new \Tk\Table\Row();
        $this->setOrderProperty($property);
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return static
     */
    public static function create($name, $label = null)
    {
        $obj = new static($name, $label);
        return $obj;
    }

    /**
     * Return the cell header HTML string
     * @return string
     */
    public function getCellHeader()
    {
        $str = str_replace(array('id', 'Id'), '', $this->getLabel());
        $url = $this->getOrderUrl();
        if ($url && $this->getTable()->getStaticOrderBy() === null) {
            $str = sprintf('<a href="%s" class="noblock" title="Click to order by: %s">%s</a>',
                htmlentities($url->toString()), $this->getOrderProperty(), $this->getLabel());
        }
        return $str;
    }

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    abstract public function getCellHtml($obj, $rowIdx = null);

    /**
     * Called when the Table::execute is called
     */
    public function execute() { }

    /**
     * Reset any persistent fields to the state of the last store() call.
     *
     * We use this method so a cell can modify its own (and row) properties and
     * they will be reset to their initial state on each row render.
     *
     * @note: Add new properties as required.
     */
    public function resetProperties()
    {
        $this->setCssList($this->store['cellCssList']);
        $this->setAttrList($this->store['cellAttrList']);
        $this->getRow()->setCssList($this->store['rowCssList']);
        $this->getRow()->setAttrList($this->store['rowAttrList']);
    }

    /**
     * Save the state of the cell's persistent fields
     *
     * We use this method so a cell can modify its own (and row) properties and
     * they will be reset to their initial state on each row render.
     *
     * @note: Add new properties as required.
     */
    public function storeProperties()
    {
        $this->store['cellCssList'] = $this->getCssList();
        $this->store['cellAttrList'] = $this->getAttrList();
        $this->store['rowCssList'] = $this->getRow()->getCssList();
        $this->store['rowAttrList'] = $this->getRow()->getAttrList();
    }

    // -------------------------------------------------------------------

    /**
     * Get the raw string property value.
     * This call can be used for exporting data into a csv, json, xml format
     *
     * @param mixed $obj
     * @return string
     */
    public function getRawValue($obj)
    {
        return $this->getPropertyValue($obj, $this->getProperty());
    }

    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     * @param object $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        $value = $this->getObjectPropertyValue($obj, $property);
        return $value;
    }

    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     * @param object $obj
     * @param string $property
     * @return mixed
     */
    protected function getObjectPropertyValue($obj, $property)
    {

        $value = '';
        if (is_array($obj)) {
            if (isset($obj[$property]))
                $value = $obj[$property];
        } else {
            if ($this->propertyExists($obj, $property)) {
                $value = $obj->{$property};
            } else {
                // Get property by method if accessor exists
                $method = 'get' . ucfirst($property);
                if (!method_exists($obj, $method)) {
                    $method = 'is' . ucfirst($property);
                }
                if (!method_exists($obj, $method)) {
                    $method = 'has' . ucfirst($property);
                }
                if (!method_exists($obj, $method)) {
                    $method = '';
                }
                if ($method) {
                    $value = $obj->$method();
                }
            }
        }
        if (is_callable($this->getOnPropertyValue()) && $this->getProperty() == $property) {
            return call_user_func_array($this->getOnPropertyValue(), array($this, $obj, $value));
        }
        return $value;
    }

    /**
     * @param object $obj
     * @param string $property
     * @return bool
     */
    private function propertyExists($obj, $property)
    {
        $exists = false;
        try {
            $class = new \ReflectionClass($obj);
            $property = $class->getProperty($property);
            if ($property->isPublic())
                $exists = true;
        } catch (\Exception $e) {
            $exists = property_exists($obj, $property);
        }
        return $exists;
    }

    /**
     * Return the provided URL with the GET parameter of the object ID
     *
     * @param mixed $obj
     * @return Uri|null
     */
    public function getCellUrl($obj)
    {
        if (!$this->url) {
            return null;
        }
        $url = Uri::create($this->getUrl());
        if ($this->urlProperty) {
            list($prop, $val) = $this->getRowPropVal($obj, $this->urlProperty);
            $url->set($prop, $val);
        }
        return $url;
    }

    /**
     * @param $obj
     * @param string $urlProperty
     * @return array|null
     */
    public function getRowPropVal($obj, $urlProperty = 'id')
    {
        $propVal = null;
        if ($urlProperty) {
            $prop = $urlProperty;
            if ($prop == 'id' && is_object($obj)) {     // If 'id' then convert to '{ObjClass}Id'
                $class = get_class($obj);
                $pos = strrpos($class, '\\');
                if (!$pos === false) {
                    $name = substr(get_class($obj), $pos + 1);
                } else {
                    $name = $class;
                }
                $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
            }
            if ($prop == '/id') // Should not be used as id params are frowned upon in urls anyway
                $urlProperty = $prop = 'id';

            $val = $this->getObjectPropertyValue($obj, $urlProperty);
            $propVal = array($prop, $val);
        }
        return $propVal;
    }

    /**
     * Create an order by url for this cell.
     *
     * @return Uri|null
     */
    public function getOrderUrl()
    {
        if (!$this->getOrderProperty()) {
            return null;
        }
        
        $order = '';
        //vd($this->getTable()->getList());
        //vd($this->getOrderProperty(), $this->getTable()->getOrderProperty());

        if ($this->getOrderProperty() == $this->getTable()->getOrderProperty()) {
            $order = $this->getTable()->getOrder();
        }
        
        $key = $this->getTable()->makeInstanceKey(Table::PARAM_ORDER_BY);
        $pre = $this->getOrderProperty() . ' ';
        $url = Uri::create();
        if ($order == Table::ORDER_ASC) {
            $url->set($key, $pre . Table::ORDER_DESC);
        } else if ($order == Table::ORDER_DESC) {
            $url->set($key, Table::ORDER_NONE);
        } else if ($order == Table::ORDER_NONE) {
            $url->set($key, $pre . Table::ORDER_ASC);
        }
        return $url;
    }


    /**
     * Set the default cell data url
     *
     *
     * @param Uri $url
     * @param string $urlProperty
     * @return $this
     */
    public function setUrl($url, $urlProperty = 'id')
    {
        $this->url = $url;
        $this->urlProperty = $urlProperty;
        return $this;
    }

    /**
     * Get the default data URL
     *
     * @return Uri|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * `id` is reserved and converted to '{objectClass}Id'
     * EG:
     *   For the class UserComment with id = 4 the following will be append
     *   to the url:
     *      'userCommentId=4'
     * The first letter of the object class is converted to lowercase and the
     * value 'Id' is appended as the property name, then the objects id parameter is
     * used for the value.
     *
     *
     * Note: `id` is not recommended to be used in public urls by convention
     *   as it can be a security issue and non SEO compliant, in simple terms
     *   just avoid using id. However if you find there is no way to avoid it,
     *   You can escape the cells default behaviour by sending '/id' instead of 'id'
     *
     * @param string $urlProperty
     * @return $this
     */
    public function setUrlProperty($urlProperty)
    {
        $this->urlProperty = $urlProperty;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlProperty()
    {
        return $this->urlProperty;
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
        // $this->init();
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
     * If set the label will be rendered
     * This depends on the table renderer being used.
     *
     * @param bool $b
     * @return $this
     */
    public function setShowLabel($b = true)
    {
        $this->showLabel = $b;
        return $this;
    }

    /**
     * getShowLabel
     *
     * @return bool
     */
    public function showLabel()
    {
        return $this->showLabel;
    }

    /**
     * Set the property that the order header uses by default this is the same as property
     *
     * @param string $orderProperty
     * @return $this
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
     * @return $this
     */
    public function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    /**
     * Get the object property name to get data from
     * the object/table
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return Table\Row
     */
    public function getRow()
    {
        return $this->row;
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

    /**
     * @return callable|null
     */
    public function getOnPropertyValue()
    {
        return $this->onPropertyValue;
    }

    /**
     * set a callback to return a modified property value
     *
     * Callback: function ($cell, $obj, $value) { return $value; }
     *
     * @param callable|null $onPropertyValue
     * @return $this
     */
    public function setOnPropertyValue($onPropertyValue)
    {
        $this->onPropertyValue = $onPropertyValue;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getOnCellHtml()
    {
        return $this->onCellHtml;
    }

    /**
     * Set the onShowCell callback
     *
     * Callback: function ($cell, $obj, $html) { return $html; }
     *
     * @param callable|null $onCellHtml
     * @return $this
     */
    public function setOnCellHtml($onCellHtml)
    {
        $this->onCellHtml = $onCellHtml;
        return $this;
    }



}
