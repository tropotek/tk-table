<?php
namespace Tk\Table\Cell;


use Tk\Callback;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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
     * @var Callback
     */
    protected $onUpdate = null;

    /**
     * @var bool
     */
    protected $iconOnly = false;


    /**
     * OrderBy constructor.
     *
     * @param string $property
     * @param null|string $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->onUpdate = Callback::create();
    }

    /**
     * @return Callback
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * @param callable|null $callable
     * @return $this
     * @deprecated use $this->getOnUpdate()->append($callable, $priority);
     */
    public function setOnUpdate($callable)
    {
        $this->getOnUpdate()->append($callable);
        return $this;
    }

    /**
     * @return bool
     */
    public function isIconOnly()
    {
        return $this->iconOnly;
    }

    /**
     * @param bool $iconOnly
     * @return OrderBy
     */
    public function setIconOnly($iconOnly = true)
    {
        if ($iconOnly)
            $this->setLabel('');
        $this->iconOnly = $iconOnly;
        return $this;
    }


    /**
     * Set table fixed order by (otherwise changing the sort order does not make any sense)
     *
     * @param \Tk\Table $table
     * @return Iface
     */
    public function setTable($table)
    {
        $table->addCss('tk-sortable');
        $table->setStaticOrderBy('order_by');
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
        /* @var \Tk\Request $request */
        $request = $this->getTable()->getRequest()->all();
        if (isset($request[$this->getTable()->makeInstanceKey('orderSwp')])) {
            if (isset($request['newOrder'])) {
                $this->doOrderUpdate($request);
            } else {
                $this->doOrderSwap($request);
            }
        }
    }

    /**
     * Swap 2 object orderBy locations
     *
     * @param $request
     * @throws \Exception
     */
    public function doOrderSwap($request)
    {
        $orderStr = $request[$this->getTable()->makeInstanceKey('orderSwp')];
        if (!preg_match('/([0-9]+)\-([0-9]+)/', $orderStr, $regs)) {
            throw new \Tk\Table\Exception('Invalid order change parameters');
        }
        $mapperClass = $this->className . 'Map';
        /* @var \Tk\Db\Mapper $mapper */
        $mapper = $mapperClass::create();

        if (!$mapper instanceof \Tk\Db\Mapper) {
            throw new \Tk\Table\Exception('Model objects must extend \Tk\Db\Mapper');
        }
        $fromObj = $mapper->find($regs[1]);
        $toObj = $mapper->find($regs[2]);

        // Probably not the best way to do this, might be fine for Debug tho
        if (\Tk\Config::getInstance()->isDebug()) {
            if (!$fromObj->{$this->getOrderProperty()} || !$toObj->{$this->getOrderProperty()}) {
                $this->resetOrder($mapper);
                \Tk\Uri::create()->redirect();
            }
        }

        if (!$fromObj || !$toObj) {
            throw new \Tk\Table\Exception('Order change object not found');
        }

        $this->orderSwap($mapper, $fromObj, $toObj);

        $this->getOnUpdate()->execute($this);
//        if (is_callable($this->onUpdate)) {
//            call_user_func_array($this->onUpdate, array($this));
//        }

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey('orderSwp'))->redirect();
    }

    /**
     * Swap 2 object orderBy locations
     *
     * @param $request
     * @throws \Exception
     */
    public function doOrderUpdate($request)
    {
        $mapperClass = $this->className . 'Map';
        /* @var \Tk\Db\Mapper $mapper */
        $mapper = $mapperClass::create();

        $orderArr = $request['newOrder'];
        $this->orderUpdate($mapper, $orderArr);

        $this->getOnUpdate()->execute($this);
//        if (is_callable($this->onUpdate)) {
//            call_user_func_array($this->onUpdate, array($this));
//        }

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey('orderSwp'))->remove('newOrder')->redirect();
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
        if ($this->isIconOnly())
            $this->addCss('icon-only');
        $this->setAttr('data-objectid', $obj->id);
        $this->setAttr('title', 'Click and drag to change order');
        $this->addCss('tk-orderBy');
//        $value = $this->getPropertyValue($obj, $this->getProperty());
        //vd($value);

        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/jquery.tableOrderBy.js'));
        $handle = $this->isIconOnly() ? 'td.tk-orderBy' : '';
        $js = <<<JS
jQuery(function($) {
  $('.tk-sortable tbody').tableOrderBy({
      selector: '.tk-sortable tbody',
      handle: '$handle'
  });
});
JS;
        $template->appendJs($js);


        $upObj = $this->getListItem($rowIdx-1);
        $dnObj = $this->getListItem($rowIdx+1);
        if ($upObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('orderSwp'), $obj->id.'-'.$upObj->id);
            $template->setAttr('upUrl', 'href', $upUrl);
        }
        if ($dnObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('orderSwp'), $obj->id.'-'.$dnObj->id);
            $template->setAttr('dnUrl', 'href', $upUrl);
        }


        if ($rowIdx == 0) {
            $template->addCss('upUrl', 'disabled');
            //$template->setAttr('upUrl', 'href', '#');
        }
        if ($this->getTable()->getList()->count() == $rowIdx+1) {
            $template->addCss('dnUrl', 'disabled');
            //$template->setAttr('dnUrl', 'href', '#');
        }
        return $template;
    }

    /**
     * Swap the order of 2 records
     *
     * @param \Tk\Db\Mapper $mapper
     * @param \Tk\Db\Map\Model $fromObj
     * @param \Tk\Db\Map\Model $toObj
     * @return int
     * @throws \Tk\Db\Exception
     */
    public function orderSwap($mapper, $fromObj, $toObj)
    {
        $property = $mapper->getDbMap()->getPropertyMap($this->getOrderProperty());
        if (!$property) {
            return 0;
        }

//        $newTo = $fromObj->{$property->getPropertyName()};
//        $newFrom = $toObj->{$property->getPropertyName()};
//        $toObj->{$property->getPropertyName()} = $newTo;
//        $fromObj->{$property->getPropertyName()} = $newFrom;
//        $fromObj->save();
//        $toObj->save();

        $pk = $mapper->getPrimaryKey();
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
            $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), (int)$toObj->{$property->getPropertyName()},
            $mapper->getDb()->quoteParameter($pk), (int)$fromObj->$pk);
        $mapper->getDb()->exec($query);
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
            $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), (int)$fromObj->{$property->getPropertyName()},
            $mapper->getDb()->quoteParameter($pk), (int)$toObj->$pk);
        $mapper->getDb()->exec($query);
        return 2;
    }

    /**
     * update all object in the array
     * array(
     *   [newPos] => [obj->id]
     *   0 => 0,
     *   1 => 1,
     *   2 => 4,
     *   3 => 2,
     *   4 => 3,
     *   5 => 5
     * );
     *
     * @param \Tk\Db\Mapper $mapper
     * @param array $updateArray
     * @return void
     * @throws \Tk\Db\Exception
     * @throws \Tk\Table\Exception
     */
    public function orderUpdate($mapper, $updateArray)
    {
        $property = $mapper->getDbMap()->getPropertyMap($this->getOrderProperty());
        if (!$property) {
            throw new \Tk\Table\Exception('OrderBy Property Not Found');
        }
        $pk = $mapper->getPrimaryKey();
        foreach ($updateArray as $order => $id) {
            $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
                $mapper->getDb()->quoteParameter($mapper->getTable()),
                $mapper->getDb()->quoteParameter($property->getColumnName()), (int)$order,
                $mapper->getDb()->quoteParameter($pk), (int)$id);
            $mapper->getDb()->exec($query);
        }
    }

    /**
     * Reset the order values to id values.
     *
     * @param \Tk\Db\Mapper $mapper
     * @return \Tk\Db\PDOStatement|null
     * @throws \Tk\Db\Exception
     */
    public function resetOrder($mapper)
    {
        $property = $mapper->getDbMap()->getPropertyMap($this->getOrderProperty());
        if (!$property) {
            return null;
        }
        $pk = $mapper->getPrimaryKey();
        $query = sprintf('UPDATE %s SET %s = %s', $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getColumnName()), $mapper->getDb()->quoteParameter($pk));
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
    <a href="javascript:;" title="Move Order Up" rel="nofollow" class="btn btn-default btn-sm btn-xs" var="upUrl"><i class="fa fa-caret-up" var="upIcon"></i></a>
    <a href="javascript:;" title="Move Order Down" rel="nofollow" class="btn btn-default btn-sm btn-xs" var="dnUrl"><i class="fa fa-caret-down" var="dnIcon"></i></a>
  </div>  
  <a href="javascript:;" title="Click And Drag" rel="nofollow" class="drag">
    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPAgMAAABGuH3ZAAAACVBMVEX///8AAAAAM8wY6EL2AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfjCwYAHAmRFEi4AAAAH0lEQVQI12NgAANGEMHqACREA2Bc/ARYHVgHI8QIBgAy+QFeo6/RgQAAAABJRU5ErkJggg==" alt="Drag"/>
  </a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}