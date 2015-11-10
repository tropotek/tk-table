<?php
namespace Tk\Table\Cell;

use Tk\Table\Table;
use Tk\Url;

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
     * @var string
     */
    protected $url = null;

    /**
     * @var Table
     */
    protected $table = null;


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
     * @param mixed $obj
     * @return string
     */
    public function getCellCsv($obj)
    {
        return $this->getPropertyValue($obj, $this->getProperty());
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
        if (is_array($obj) && isset($obj[$property])) {
            return $obj[$property];
        }
        // Try to return property
        if (property_exists($obj, $property)) {
            return $obj->{$property};
        }
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
            return $obj->$method();
        }
        return '--';
    }

    /**
     * Return the provided URL with the GET parameter of the object ID
     *
     * @param mixed $obj
     * @param string $property
     * @return Url|null
     */
    public function getCellUrl($obj, $property = 'id')
    {
        if (!$this->url) {
            return null;
        }

        $url = Url::create($this->getUrl());
        $prop = $property;
        if ($property = 'id') {
            $class = get_class($obj);
            $pos = strrpos($class, '\\');
            if (!$pos === false) {
                $name = substr(get_class($obj), $pos + 1);
            } else {
                $name = $class;
            }
            $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
        }
        $url->set($prop, $this->getPropertyValue($obj, $property));
        return $url;
    }

    /**
     * Create an order by url for this cell.
     *
     * @return Url|null
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
        $key = $this->getTable()->makeInstanceKey(\Tk\Db\Mapper::PARAM_ORDER_BY);
        $pre = $this->getOrderProperty() . ' ';
        $url = Url::create();
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
     * @param Url $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the default data URL
     *
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     */
    public function setTable($table)
    {
        $this->table = $table;
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
     * @return array
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
     * @return array
     */
    public function setCellCssList($arr = array())
    {
        $this->cellCssList = $arr;
        return $this;
    }

}
