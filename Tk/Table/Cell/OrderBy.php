<?php
namespace Tk\Table\Cell;


/**
 * Class OrderBy
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class OrderBy extends Text
{


    /**
     * The object class name we are ordering
     * @var string
     */
    protected $className = '';



    /**
     * OrderBy constructor.
     *
     * @param string $property
     * @param null|string $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
    }

    /**
     * Set table fixed order by (otherwise changing the sort order does not make any sense)
     *
     * @param \Tk\Table $table
     * @return Iface
     */
    public function setTable($table)
    {
        $table->addCssClass('tk-sortable');
        $table->setFixedOrderBy('orderBy');
        return parent::setTable($table);
    }

    /**
     *
     */
    public function execute()
    {
        $list = $this->getTable()->getList();
        $obj = current($list);
        if ($list instanceof \Tk\Db\Map\ArrayObject) {
            $obj = $list->current();
        }
        if ($obj) {
            $this->className = get_class($obj);
        }
        /** @var \Tk\Request $request */
        $request = $this->getTable()->getRequest();
        if (isset($request[$this->getTable()->makeInstanceKey('doOrderId')])) {
            $this->doOrder($request);
        }
    }

    public function doOrder($request)
    {
        $orderStr = $request[$this->getTable()->makeInstanceKey('doOrderId')];
        if (!preg_match('/([0-9]+)\-([0-9]+)/', $orderStr, $regs)) {
            throw new \Tk\Table\Exception('Invalid order change parameters');
        }
        $mapperClass = $this->className . 'Map';
        /** @var \Ts\Db\Mapper $mapper */
        $mapper = $mapperClass::create();

        // TODO: This may be a bit un-secure to leave here....
        if ($regs[1] == 0 || $regs[2] == 0) {
            $this->resetOrder($mapper);
        }

        if (!$mapper instanceof \Ts\Db\Mapper) {
            throw new \Tk\Table\Exception('Model objects must extend \Ts\Db\Mapper');
        }
        $fromObj = $mapper->find($regs[1]);
        $toObj = $mapper->find($regs[2]);

        if (!$fromObj || !$toObj) {
            throw new \Tk\Table\Exception('Order change object not found');
        }

        $this->orderSwap($mapper, $fromObj, $toObj);

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey('doOrderId'))->redirect();

    }

    /**
     * May be a good method for the table itself????
     *
     * @param $idx
     * @return mixed|null
     */
    public function getListItem($idx)
    {
        $list = $this->getTable()->getList();
        if ($list instanceof \Tk\Db\Map\ArrayObject) {
            return $list->get($idx);
        }
        if (isset($list[$idx])) return $list[$idx];
        return null;
    }

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $template = $this->__makeTemplate();
        $value = $this->getPropertyValue($obj, $this->getProperty());
        //vd($value);

        $upObj = $this->getListItem($rowIdx-1);
        $dnObj = $this->getListItem($rowIdx+1);
        if ($upObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('doOrderId'), $obj->id.'-'.$upObj->id);
            $template->setAttr('upUrl', 'href', $upUrl);
        }
        if ($dnObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('doOrderId'), $obj->id.'-'.$dnObj->id);
            $template->setAttr('dnUrl', 'href', $upUrl);
        }


        if ($rowIdx == 0) {
            $template->addClass('upUrl', 'disabled');
            $template->setAttr('upUrl', 'href', '#');
        }
        if ($this->getTable()->getList()->count() == $rowIdx+1) {
            $template->addClass('dnUrl', 'disabled');
            $template->setAttr('dnUrl', 'href', '#');
        }
        return $template;
    }

    /**
     * Swap the order of 2 records
     *
     * @param \Ts\Db\Mapper $mapper
     * @param \Tk\Db\Map\Model $fromObj
     * @param \Tk\Db\Map\Model $toObj
     * @return int
     */
    public function orderSwap($mapper, $fromObj, $toObj)
    {
        $property = $mapper->getDbMap()->getProperty($this->getOrderProperty());
        if (!$property) {
            return 0;
        }
        $pk = $mapper->getPrimaryKey();
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
            $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), $mapper->getDb()->quote($toObj->{$property->getPropertyName()}),
            $mapper->getDb()->quoteParameter($pk), (int)$fromObj->$pk);
        $mapper->getDb()->exec($query);
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d', $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), $mapper->getDb()->quote($fromObj->{$property->getPropertyName()}),
            $mapper->getDb()->quoteParameter($pk), (int)$toObj->$pk);
        $mapper->getDb()->exec($query);
        return 2;
    }

    /**
     * Reset the order values to id values.
     *
     * @param \Ts\Db\Mapper $mapper
     * @return \Tk\Db\PDOStatement|null
     */
    public function resetOrder($mapper)
    {
        $property = $mapper->getDbMap()->getProperty($this->getOrderProperty());
        if (!$property) {
            return null;
        }
        $pk = $mapper->getPrimaryKey();
        $query = sprintf('UPDATE %s SET %s = %s', $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), $mapper->getDb()->quoteParameter($pk));
        vd($query);
        return $mapper->getDb()->exec($query);
    }


    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="text-center">
  <div class="btn-group" role="group">
    <a href="javascript:;" title="Move Order Up" rel="nofollow" class="btn btn-default btn-xs" var="upUrl"><i class="fa fa-caret-up" var="upIcon"></i></a>
    <a href="javascript:;" title="Move Order Down" rel="nofollow" class="btn btn-default btn-xs" var="dnUrl"><i class="fa fa-caret-down" var="dnIcon"></i></a>
  </div>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}