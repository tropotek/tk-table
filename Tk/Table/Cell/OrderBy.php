<?php
namespace Tk\Table\Cell;

use Tk\Db;
use Tk\Db\Model;
use Tk\Exception;
use Tk\Log;
use Tk\Table;
use Tk\Table\Cell;
use Tk\Uri;

/**
 * This cell only works with an array of `\Tk\Db\Model` objects
 *
 */
class OrderBy extends Cell
{
    protected string $modelClass = '';


    public function __construct(string $name, string $modelClass)
    {
        parent::__construct($name);
        $this->addCss('tk-orderBy');
        if (!(class_exists($modelClass) && is_subclass_of($modelClass, Model::class))) {
            throw new Exception("Invalid Model class value");
        }
        $this->modelClass = $modelClass;
    }

    public static function create(string $name, string $modelClass): self
    {
        return new self($name, $modelClass);
    }

    protected function execute(): void
    {
        $orderSwapKey = $this->getTable()->makeRequestKey('orderSwap');
        if (!isset($_GET[$orderSwapKey])) return;

        if (isset($_POST['newOrder'])) {
            $this->doOrderUpdate();
        } else {
            $this->doOrderSwap();
        }

        Uri::create()->remove($orderSwapKey)->remove('newOrder')->redirect();
    }

    public function doOrderSwap(): void
    {
        $orderSwapKey = $this->getTable()->makeRequestKey('orderSwap');
        $orderStr = $_GET[$orderSwapKey] ?? '';

        if (!preg_match('/([0-9]+)\-([0-9]+)/', $orderStr, $regs)) {
            throw new Exception('Invalid order change parameters');
        }

        $fromObj = $this->modelClass::find(intval($regs[1] ?? 0));
        $toObj   = $this->modelClass::find(intval($regs[2] ?? 0));
        if (!($fromObj && $toObj)) {
            return;
        }

        $map     = $fromObj::getDataMap();
        $table   = $fromObj::getDbTable();
        $col     = $map->getTypeByProperty($this->getName())->getColumn();
        $prop    = $map->getTypeByProperty($this->getName())->getProperty();
        $priCol  = $map->getPrimaryKey()->getColumn();
        $priProp = $map->getPrimaryKey()->getProperty();

        $ok = Db::update($table, $priCol, [$priCol => (int)$fromObj->$priProp, $col => (int)$toObj->$prop]);
        if ($ok === false) {
            Log::error("failed to update order on {$table} for id {$toObj->$prop}");
            return;
        }

        $ok = Db::update($table, $priCol, [$priCol => (int)$toObj->$priProp, $col => (int)$fromObj->$prop]);
        if ($ok === false) {
            Log::error("failed to update order on {$table} for id {$fromObj->$prop}");
            return;
        }
    }

    public function doOrderUpdate(): void
    {
        $orderArr = $_POST['newOrder'] ?? [];
        if (empty($orderArr)) return;

        $map     = $this->modelClass::getDataMap();
        $col     = $map->getTypeByProperty($this->getName())->getColumn();
        $priCol  = $map->getPrimaryKey()->getColumn();

        foreach ($orderArr as $order => $id) {
            Db::update(
                $this->modelClass::getDbTable(),
                $priCol,
                [
                    $priCol => (int)$id,
                    $col => (int)$order
                ]
            );
        }
    }

    public function getValue(array|object $row): string
    {
        /** @var Model $row */
        if (!$row instanceof Model) {
            Log::warning(self::class . " only works with " . Model::class . " objects");
            return '';
        }

        $key = $this->getTable()->makeRequestKey('orderSwap');
        $prevUrl = '#';
        $prevAttr = 'disabled="disabled"';
        $prevCss = 'disabled';
        $nextUrl = '#';
        $nextAttr = 'disabled="disabled"';
        $nextCss = 'disabled';
        $prev = $this->getPrevItem($row);
        $next = $this->getNextItem($row);
        if ($prev) {
            $prevUrl = \Tk\Uri::create(null, [$key => $row->getId().'-'.$prev->getId()])->toString();
            $prevAttr = '';
            $prevCss = '';
        }
        if ($next) {
            $nextUrl = \Tk\Uri::create(null, [$key => $row->getId().'-'.$next->getId()])->toString();
            $nextAttr = '';
            $nextCss = '';
        }

        $this->setAttr('title', 'Click or drag to change order');
        $this->addCss('align-middle p-1');
        $this->setAttr('data-orderby-id', $row->getId());

        return <<<HTML
<div class="text-center p-0">
  <div title="Click And Drag" rel="nofollow" class="float-start drag">
    <i class="fas fa-grip-vertical"></i>
  </div>
  <div class="btn-group ms-1" role="group">
    <a href="{$prevUrl}" {$prevAttr} title="Prev" rel="nofollow" class="btn btn-outline-secondary btn-sm {$prevCss}"><i class="fa fa-caret-up"></i></a>
    <a href="{$nextUrl}" {$nextAttr} title="Next" rel="nofollow" class="btn btn-outline-secondary btn-sm {$nextCss}"><i class="fa fa-caret-down"></i></a>
  </div>
</div>
HTML;
    }

    public function setTable(?Table $table): Cell
    {
        $table->addCss('tk-sortable');
        parent::setTable($table);
        $this->execute();
        return $this;
    }

    protected function getPrevItem(Model $curr): ?Model
    {
        $map   = $curr->getDataMap();
        $table = $curr::getDbTable();
        $col   = $map->getTypeByProperty($this->getName())->getColumn();
        $prop  = $map->getTypeByProperty($this->getName())->getProperty();

        $next = Db::queryOne("
            SELECT *
            FROM $table
            WHERE $col < :$prop
            ORDER BY $col DESC
            LIMIT 1",
            $curr,
            get_class($curr)
        );

        if ($next instanceof Model) return $next;
        return null;
    }

    protected function getNextItem(Model $curr): ?Model
    {
        $map   = $curr->getDataMap();
        $table = $curr::getDbTable();
        $col   = $map->getTypeByProperty($this->getName())->getColumn();
        $prop  = $map->getTypeByProperty($this->getName())->getProperty();

        $next = Db::queryOne("
            SELECT *
            FROM $table
            WHERE $col > :$prop
            ORDER BY $col
            LIMIT 1",
            $curr,
            get_class($curr)
        );

        if ($next instanceof Model) return $next;
        return null;
    }

}