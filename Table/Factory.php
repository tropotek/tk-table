<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table;

/**
 * The Table Factory is a place to crate all
 * elements to use a table.
 *
 * Its primary purpose is to give extendability using observers to override
 * elements upon creation.
 *
 *
 * @package Table
 */
class Factory extends \Tk\Object
{
    /**
     * @var Factory
     */
    static $instance = null;

    /**
     * @var mixed
     */
    protected $object = null;





    /**
     * Get an instance of this object
     *
     * @return Factory
     */
    static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * This function allows observers to access the current
     * created object for modification/replacement if required.
     *
     * @return type
     */
    public function getCurrentObject()
    {
        return $this->object;
    }


    //-------------- FACTORY METHODS ------------------
    // Try to list them in alphabetical order please....


    /**
     * Create a table
     *
     * @param $id
     * @return Table
     */
    public function createTable($id)
    {
        $this->object = new Table($id);
        $this->notify('createTable');
        return $this->object;
    }

    /**
     * Create Table Renderer
     *
     * @param Table $table
     * @return Renderer
     */
    public function createTableRenderer(Table $table)
    {
        $this->object = new Renderer($table);
        $this->notify('createTableRenderer');
        return $this->object;
    }

    /**
     * Create Table Renderer
     *
     * @param Table $table
     * @return Tree\Renderer
     */
    public function createTableTreeRenderer(Table $table)
    {
        $this->object = new Tree\Renderer($table);
        $this->notify('createTableTreeRenderer');
        return $this->object;
    }




    // ----------- TABLE ACTIONS ---------------


    /**
     * Create
     *
     * @return Action\Csv
     */
    public function createActionCsv()
    {
        $this->object = Action\Csv::create();
        $this->notify('createActionCsv');
        return $this->object;
    }

    /**
     * Create
     *
     * @return Action\Delete
     */
    public function createActionDelete()
    {
        $this->object = Action\Delete::create();
        $this->notify('createActionDelete');
        return $this->object;
    }

    /**
     * Create
     *
     * @return Action\FieldVis
     */
    public function createActionFieldVis()
    {
        $this->object = Action\FieldVis::create();
        $this->notify('createActionFieldVis');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $label
     * @param \Tk\Url $url
     * @param string $class
     * @param string $confirmMsg
     * @return Action\Url
     */
    public function createActionUrl($label, $url, $class = '', $confirmMsg = '')
    {
        $this->object = Action\Url::create($label, $url, $class, $confirmMsg);
        $this->notify('createActionUrl');
        return $this->object;
    }






    // ----------- TABLE CELL ---------------

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Boolean
     */
    public function createCellBoolean($property, $name = '')
    {
        $this->object = new Cell\Boolean($property, $name);
        $this->notify('createCellBoolean');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Bytes
     */
    public function createCellBytes($property, $name = '')
    {
        $this->object = new Cell\Bytes($property, $name);
        $this->notify('createCellBytes');
        return $this->object;
    }

    /**
     * Create
     *
     * @return Cell\Checkbox
     */
    public function createCellCheckbox()
    {
        $this->object = new Cell\Checkbox();
        $this->notify('createCellCheckbox');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\DataImage
     */
    public function createCellDataImage($property, $name = '')
    {
        $this->object = new Cell\DataImage($property, $name);
        $this->notify('createCellDataImage');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Date
     */
    public function createCellDate($property, $name = '', $format = '')
    {
        if (!$format) $format = \Tk\Date::MED_DATE;
        $this->object = new Cell\Date($property, $name);
        $this->object->setFormat($format);
        $this->notify('createCellDate');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\TimeLapse
     */
    public function createCellTimeLapse($property, $name = '')
    {
        $this->object = new Cell\TimeLapse($property, $name);
        $this->notify('createCellTimeLapse');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Email
     */
    public function createCellEmail($property, $name = '')
    {
        $this->object = new Cell\Email($property, $name);
        $this->notify('createCellEmail');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @param int $places The number of decimal places
     * @return Cell\Float
     */
    public function createCellFloat($property, $name = null, $places = 2)
    {
        $this->object = new Cell\Float($property, $name, $places);
        $this->notify('createCellFloat');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Integer
     */
    public function createCellInteger($property, $name = null)
    {
        $this->object = new Cell\Integer($property, $name);
        $this->notify('createCellInteger');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Money
     */
    public function createCellMoney($property, $name = null)
    {
        $this->object = new Cell\Money($property, $name);
        $this->notify('createCellMoney');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\OrderBy
     */
    public function createCellOrderBy($property, $name = null)
    {
        $this->object = new Cell\OrderBy($property, $name);
        $this->notify('createCellOrderBy');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\String
     */
    public function createCellString($property, $name = null)
    {
        $this->object = new Cell\String($property, $name);
        $this->notify('createCellString');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return Cell\Url
     */
    public function createCellUrl($property, $name = null)
    {
        $this->object = new Cell\Url($property, $name);
        $this->notify('createCellUrl');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $property
     * @param string $name
     * @return EditCell\String
     */
    public function createEditCellString($property, $name = null)
    {
        $this->object = new EditCell\String($property, $name);
        $this->notify('createEditCellString');
        return $this->object;
    }



    


    // ----------- TABLE EVENT ---------------

    /**
     * Create
     *
     * @param Table $table
     * @return Event\Clear
     */
    public function createEventClear(Table $table)
    {
        $this->object = new Event\Clear($table);
        $this->notify('createEventClear');
        return $this->object;
    }

    /**
     * Create
     *
     * @param string $name
     * @param Table $table
     * @return Event\Search
     */
    public function createEventSearch($name, Table $table)
    {
        $this->object = new Event\Search($name, $table);
        $this->notify('createEventSearch');
        return $this->object;
    }




}
