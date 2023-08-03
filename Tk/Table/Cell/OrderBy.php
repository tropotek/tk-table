<?php
namespace Tk\Table\Cell;

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\CallbackCollection;
use Tk\Db\Mapper\Mapper;
use Tk\Db\Mapper\Model;
use Tk\Table;
use Tk\Uri;

/**
 * @todo: Maybe move this to the Bs lib with its required javascript
 * @todo: also add the columns action button too.
 */
class OrderBy extends Text
{

    /**
     * The object class name we are ordering
     */
    protected string $className = '';

    protected CallbackCollection $onUpdate;

    protected bool $iconOnly = false;


    public function __construct(string $property, string $label = '')
    {
        $this->onUpdate = new CallbackCollection();
        parent::__construct($property, $label);
        $this->setIconOnly(true);
    }

    public function getOnUpdate(): CallbackCollection
    {
        return $this->onUpdate;
    }

    public function addOnUpdate(callable $callable): static
    {
        $this->getOnUpdate()->append($callable);
        return $this;
    }

    public function isIconOnly(): bool
    {
        return $this->iconOnly;
    }

    public function setIconOnly(bool $iconOnly = true): static
    {
        if ($iconOnly) $this->setLabel('');
        $this->iconOnly = $iconOnly;
        return $this;
    }

    /**
     * Set table fixed order by (otherwise changing the sort order does not make any sense)
     */
    public function setTable(Table $table): static
    {
        $table->addCss('tk-sortable');
        return parent::setTable($table);
    }

    public function getCellValue(): string
    {
        $template = $this->getTemplate();

        $obj = $this->getRow()->getData();
        $rowIdx = $this->getRow()->getId();
        if (!$obj instanceof Model) return $template;

        if ($this->isIconOnly())
            $this->addCss('icon-only');

        $this->setAttr('data-id', $obj->getId());

        $this->setAttr('title', 'Click and drag to change order');
        $this->addCss('tk-orderBy');

        $jsUrl = \Tk\Uri::create($this->getConfig()->get('path.vendor.org') . '/tk-table/js/jquery.tableOrderBy.js');
        $template->appendJsUrl($jsUrl);

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

        $upObj = $this->getTable()->getListItem($rowIdx-1);
        $dnObj = $this->getTable()->getListItem($rowIdx+1);
        if ($upObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('orderSwp'), $obj->getId().'-'.$upObj->getId());
            $template->setAttr('upUrl', 'href', $upUrl);
        }
        if ($dnObj) {
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('orderSwp'), $obj->getId().'-'.$dnObj->getId());
            $template->setAttr('dnUrl', 'href', $upUrl);
        }

        if ($rowIdx == 0) {
            $template->addCss('upUrl', 'disabled');
        }
        if ($this->getTable()->getList()->count() == $rowIdx+1) {
            $template->addCss('dnUrl', 'disabled');
        }
        $this->setUrlProperty('');

        return '';
    }

    public function execute(Request  $request): void
    {
        // Disable all cell sort params
        /** @var CellInterface $cell */
        foreach ($this->getTable()->getCells() as $cell) {
            $cell->setOrderByName('');
        }

        // Try and determine list object classname
        $obj = $this->getTable()->getListItem(0);
        if ($obj && is_object($obj)) $this->className = get_class($obj);

        if ($request->request->has($this->getTable()->makeInstanceKey('orderSwp'))) {
            if ($request->request->has('newOrder')) {
                $this->doOrderUpdate($request);
            } else {
                $this->doOrderSwap($request);
            }
        }
    }

    /**
     * Swap 2 object orderBy locations
     */
    public function doOrderSwap(Request $request): void
    {
        $orderStr = $request[$this->getTable()->makeInstanceKey('orderSwp')];
        if (!preg_match('/([0-9]+)\-([0-9]+)/', $orderStr, $regs)) {
            throw new \Tk\Table\Exception('Invalid order change parameters');
        }
        $mapperClass = $this->className . 'Map';
        /** @var Mapper $mapper */
        $mapper = $mapperClass::create();
        if (!$mapper instanceof Mapper) {
            return;
        }

        $fromObj = $mapper->find($regs[1]);
        $toObj = $mapper->find($regs[2]);

        // Probably not the best way to do this, might be fine for Debug tho
        if ($this->getConfig()->isDebug()) {
            if (!$fromObj->{$this->getOrderByName()} || !$toObj->{$this->getOrderByName()}) {
                $this->resetOrder($mapper);
                \Tk\Uri::create()->redirect();
            }
        }

        if (!$fromObj || !$toObj) {
            throw new \Tk\Table\Exception('Order change object not found');
        }

        $this->orderSwap($mapper, $fromObj, $toObj);

        $this->getOnUpdate()->execute($this);

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey('orderSwp'))->redirect();
    }

    /**
     * Swap 2 object orderBy locations
     */
    public function doOrderUpdate(Request $request): void
    {
        $mapperClass = $this->className . 'Map';
        /* @var Mapper $mapper */
        $mapper = $mapperClass::create();

        $orderArr = $request['newOrder'];
        $this->orderUpdate($mapper, $orderArr);

        $this->getOnUpdate()->execute($this);

        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey('orderSwp'))->remove('newOrder')->redirect();
    }

    /**
     * Swap the order of 2 records
     */
    public function orderSwap(Mapper $mapper, Model $fromObj, Model $toObj): int
    {
        $property = $mapper->getDbMap()->getPropertyType($this->getOrderByName());
        if (!$property) {
            return 0;
        }

        $pk = $mapper->getPrimaryType()->getKey();
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
            $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getKey()), (int)$toObj->{$property->getProperty()},
            $mapper->getDb()->quoteParameter($pk), (int)$fromObj->$pk);
        $mapper->getDb()->exec($query);
        $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
            $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getKey()), (int)$fromObj->{$property->getProperty()},
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
     */
    public function orderUpdate(Mapper $mapper, array $updateArray): void
    {
        $property = $mapper->getDbMap()->getPropertyType($this->getOrderByName());
        if (!$property) {
            throw new \Tk\Table\Exception('OrderBy Property Not Found');
        }
        $pk = $mapper->getPrimaryType()->getKey();
        foreach ($updateArray as $order => $id) {
            $query = sprintf('UPDATE %s SET %s = %s WHERE %s = %d',
                $mapper->getDb()->quoteParameter($mapper->getTable()),
                $mapper->getDb()->quoteParameter($property->getKey()), (int)$order,
                $mapper->getDb()->quoteParameter($pk), (int)$id);
            $mapper->getDb()->exec($query);
        }
    }

    /**
     * Reset the order values to id values.
     */
    public function resetOrder(Mapper $mapper): false|int|null
    {
        $property = $mapper->getDbMap()->getPropertyType($this->getOrderByName());
        if (!$property) {
            return null;
        }
        $pk = $mapper->getPrimaryType()->getKey();
        $query = sprintf('UPDATE %s SET %s = %s', $mapper->getDb()->quoteParameter($mapper->getTable()),
            $mapper->getDb()->quoteParameter($property->getKey()), $mapper->getDb()->quoteParameter($pk));
        return $mapper->getDb()->exec($query);
    }

    /**
     * Disable the URL for this cell
     */
    public function setUrl(null|string|Uri $url): static
    {
        return $this;
    }

}