<?php

namespace Tk;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tk\Table\Action\ActionInterface;
use Tk\Traits\EventDispatcherTrait;
use Tk\Ui\Element;
use Symfony\Component\HttpFoundation\Request;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\Table\Event\TableEvent;
use Tk\Table\TableBag;
use Tk\Table\TableEvents;
use Tk\Table\TableSession;
use Tk\Table\Cell\CellInterface;
use Tk\Table\Row;

/**
 *  Add ?rts={id} to the URL request to reset this table session.
 *  And ?rts=rts to reset all table sessions on the page
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Table extends Element implements InstanceKey
{
    use EventDispatcherTrait;

    /**
     * This is the query string to set to reset the table session
     */
    const RESET_TABLE = 'rts';

    protected string $id = '';

    /**
     * A row to set the defaults and will be cloned
     * on each row render
     */
    private Row $row;

    /**
     * The default cells that will be cloned
     * on each row render
     */
    protected Collection $cells;

    protected Collection $actions;

    protected array|Result $list;


    public function __construct(string $tableId = '')
    {
        $this->row     = new Row($this);
        $this->cells   = new Collection();
        $this->actions = new Collection();

        $this->setDispatcher($this->getFactory()->getEventDispatcher());

        if (!$tableId) {
            $uri = \Tk\Uri::create();
            $uri = str_replace('.'.$uri->getExtension(), '', $uri->basename());
            $tableId = trim(strtolower(preg_replace('/[A-Z]/', '-$0', $uri . \Tk\ObjectUtil::basename(get_class($this)) )), '-');
        }
        $this->setId($tableId);
    }

    /**
     * Execute any Table and cell request responses
     */
    public function execute(Request $request)
    {
        /** @var CellInterface $cell */
        foreach ($this->getCells() as $cell) {
            $cell->execute($request);
        }

        /* @var ActionInterface $action */
        foreach ($this->getActions() as $action) {
            $action->init();
            $action->execute($request);
        }

        if ($request->query->has(self::RESET_TABLE) && $request->query->has(self::RESET_TABLE) == $this->getId()) {
            $this->resetTableSession();
            \Tk\Uri::create()->remove(self::RESET_TABLE)->redirect();
        }
        $this->getDispatcher()?->dispatch(new TableEvent($this), TableEvents::TABLE_EXECUTE);
    }

    /**
     * Set the data list
     */
    public function setList(array|Result $list, ?int $rowTotal = null): static
    {
        $this->list = $list;
        if (!$rowTotal) {
            $rowTotal = count($list);
            if ($list instanceof Result) $rowTotal = $list->countAll();
        }
        $this->getTableSession()->setRowTotal($rowTotal);

        // TODO: Not sure if this should happen here or should it be called manually
        //       See how this goes over time
        if ($list instanceof Result) $this->autofillOrderBy();

        $this->getDispatcher()?->dispatch(new TableEvent($this), TableEvents::TABLE_INIT);

        return $this;
    }

    /**
     * Get the data list
     */
    public function getList(): null|array|Result
    {
        return $this->list;
    }

    public function autofillOrderBy(null|array|Result $list = null): static
    {
        $list = $list ?? $this->getList();
        if (!$list) throw new \Tk\Table\Exception('Cannot autofill orderBy names without setting the list first.');

        if ($list instanceof Result) {
            $dbMap = $list->getMapper()->getDbMap();
            // Auto populate orderBy fields
            /** @var CellInterface $cell */
            foreach ($this->getCells() as $cell) {
                if (!$cell->getOrderByName()) {
                    $cell->setOrderByName($dbMap->getPropertyType($cell->getName())?->getKey() ?? '');
                }
            }
        } else {
            /** @var CellInterface $cell */
            foreach ($this->getCells() as $cell) {
                if (!$cell->getOrderByName()) {
                    $cell->setOrderByName($cell->getName());
                }
            }
        }

        return $this;
    }

    /**
     * ensure the id is unique
     */
    protected function setId($id): static
    {
        static $instances = [];
        if ($this->getId()) return $this;
        if (!isset($instances[$id])) {
            $instances[$id] = 0;
        } else {
            $instances[$id]++;
        }
        if ($instances[$id] > 0) $id = $id.$instances[$id];
        $this->id = $id;
        $this->setAttr('id', $this->getId());
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Create request keys with prepended string
     * returns: `{id}-{$key}`
     */
    public function makeInstanceKey($key): string
    {
        return $this->getId() . '-' . $key;
    }

    public function getRow(): Row
    {
        return $this->row;
    }


    public function getCells(): Collection
    {
        return $this->cells;
    }

    public function appendCell(CellInterface $cell, ?string $refName = null): CellInterface
    {
        if ($this->getCells()->has($cell->getName())) {
            throw new \Tk\Table\Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->append($cell->getName(), $cell, $refName);
    }

    public function prependCell(CellInterface $cell, ?string $refName = null)
    {
        if ($this->getCells()->has($cell->getName())) {
            throw new \Tk\Table\Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->prepend($cell->getName(), $cell, $refName);
    }

    public function removeCell($cellName): static
    {
        $this->getCells()->remove($cellName);
        return $this;
    }

    public function getCell(string $name): ?CellInterface
    {
        return $this->getCells()->get($name);
    }


    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function appendAction(ActionInterface $action, ?string $refName = null): ActionInterface
    {
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Table\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->append($action->getName(), $action, $refName);
    }

    public function prependAction(CellInterface $action, ?string $refName = null)
    {
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Table\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->prepend($action->getName(), $action, $refName);
    }

    public function removeAction($actionName): static
    {
        $this->getActions()->remove($actionName);
        return $this;
    }

    public function getAction(string $name): ?ActionInterface
    {
        return $this->getActions()->get($name);
    }


    public function getTableSession(): TableSession
    {
        return TableBag::getTableSession($this->getId());
    }

    public function resetTableSession(): static
    {
        \Tk\Log::warning('Resetting Table Session.');
        TableBag::removeTableSession($this->getId());
        return $this;
    }

    public function getTool(string $defaultOrderBy = '', int $defaultLimit = 0): Tool
    {
        return $this->getTableSession()->getTool($defaultOrderBy, $defaultLimit);
    }

}