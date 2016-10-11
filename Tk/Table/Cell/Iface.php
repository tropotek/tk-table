<?php
namespace Tk\Table\Cell;

use Tk\Table;
use Tk\Uri;

/**
 * The interface for a table Cell
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface
{

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
     * This is the row object's property name to access
     * it could be a getter starting with is, get, has or a public property
     * @var string
     */
    protected $property = '';

    /**
     * All classes appended to this tableData cell
     * @var array
     */
    protected $cellCssList = array();

    /**
     * Any classes to append to this cell's parent <tr>
     * @var array
     */
    protected $rowCssList = array();

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
     * The max numbers of characters to display
     *      0 = no limit
     * @var int
     */
    protected $charLimit = 0;


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
     * @return string
     */
    abstract public function getCellHeader();

    /**
     * @param mixed $obj
     * @return string
     */
    abstract public function getCellHtml($obj);

    /**
     * Reset any persistent fields that need to be non-persistant.
     * For things like css classes, attributes, etc...
     * As some fields can be changed on a per-row basis within the cells themselves
     *
     */
    public function reset()
    {
        $this->setCellCssList(array());
        $this->setRowCssList(array());
    }

    /**
     * @param mixed $obj
     * @return string
     */
    public function getCellCsv($obj)
    {
        return $this->getPropertyValue($obj, $this->getProperty());
    }

    /**
     * Use 0 to disable character limit
     *
     * @param $i
     * @return $this
     */
    public function setCharacterLimit($i)
    {
        $this->charLimit = (int)$i;
        return $this;
    }

    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     *
     * @param object $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        return $this->getObjectPropertyValue($obj, $property);
    }

    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     *
     * @param object $obj
     * @param string $property
     * @return mixed
     */
    protected function getObjectPropertyValue($obj, $property)
    {
        $value = '';
        if (is_array($obj) && isset($obj[$property])) {
            $value = $obj[$property];
        } else {
            // Try to return property
            if (property_exists($obj, $property)) {
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
        if ($this->charLimit) {
            $value = substr($value, 0, $this->charLimit);
        }
        return $value;
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
            $prop = $this->urlProperty;
            if ($prop == 'id') {     // If 'id' then convert to '{ObjClass}Id'
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
                $this->urlProperty = $prop = 'id';

            $val = $this->getObjectPropertyValue($obj, $this->urlProperty);
            if ($val)
                $url->set($prop, $val);
        }
        return $url;
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
     * @param Uri $url
     * @param string $urlProperty TODO: Should this be set to null and all objects forced to set this. Leave now untill we are sure.
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
     * Add a row css class
     *
     * @param string $class
     * @return $this
     */
    public function addRowCss($class)
    {
        $this->rowCssList[$class] = $class;
        return $this;
    }

    /**
     * remove a row css class
     *
     * @param string $class
     * @return $this
     */
    public function removeRowCss($class)
    {
        unset($this->rowCssList[$class]);
        return $this;
    }

    /**
     * Get the css row class list
     *
     * @return array
     */
    public function getRowCssList()
    {
        return $this->rowCssList;
    }

    /**
     * Set the css row class list
     * If no parameter sent the array is cleared.
     *
     * @param array $arr
     * @return $this
     */
    public function setRowCssList($arr = array())
    {
        $this->rowCssList = $arr;
        return $this;
    }

    /**
     * Add a cell css class
     *
     * @param string $class
     * @return $this
     */
    public function addCellCss($class)
    {
        $this->cellCssList[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return $this
     */
    public function removeCellCss($class)
    {
        unset($this->cellCssList[$class]);
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    public function getCellCssList()
    {
        return $this->cellCssList;
    }

    /**
     * Set the css cell class list
     * If no parameter sent the array is cleared.
     *
     * @param array $arr
     * @return $this
     */
    public function setCellCssList($arr = array())
    {
        $this->cellCssList = $arr;
        return $this;
    }

}
