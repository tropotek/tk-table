<?php

namespace Tk;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Dom\Renderer\Traits\AttributesTrait;
use Dom\Renderer\Traits\CssTrait;
use Symfony\Component\HttpFoundation\Request;
use Tk\Event\TableEvent;
use Tk\Table\TableEvents;
use Tk\Traits\SystemTrait;
use Tk\Table\Cell\CellInterface;
use Tk\Table\Row;


    // TODO:
    //     I have had a thought here, could we implement some sort of adaper/decorator pattern here
    //     Where we send the list to the list adapter and send the adapter to the table.
    //     We could create an adapter for arrays, the Db\Map\Results object, and others.
    //     Create an interface for it then we can create different types of lists as needed.
    //     Then maybe the DB adapter can manage the Db\Tool, DB\Mapper and paging then the Table does not need to know
    //     about these abstract objects...
    // TODO:
    //     Create a test and see what reveals from it, also look into how we handle sorting of columns

    //  TODO:
    //     Add the session into a symfony session bag and that may take care of a lot of session code in here



/**
 *  Add ?rts={id} to the URL request to reset this table session.
 *  And ?rts=rts to reset all table sessions on the page
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Table implements InstanceKey
{
    use AttributesTrait;
    use CssTrait;
    use SystemTrait;

    /**
     * This is the query string to set to reset the table session
     */
    const RESET_TABLE = 'rts';

    protected ?EventDispatcherInterface $dispatcher = null;

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

    protected array $list = [];


    public function __construct(string $tableId = '')
    {
        $this->row = new Row($this);
        $this->cells = new Collection();
        if ($this->getFactory()->getEventDispatcher()) {
            $this->dispatcher = $this->getFactory()->getEventDispatcher();
        }

        if (!$tableId) {
            $uri = \Tk\Uri::create();
            $uri = str_replace('.'.$uri->getExtension(), '', $uri->basename());
            $tableId = trim(strtolower(preg_replace('/[A-Z]/', '-$0', $uri . \Tk\ObjectUtil::basename(get_class($this)) )), '-');
        }
        $this->setId($tableId);
    }


    /**
     * Set the data list
     */
    public function setList(mixed $list): static
    {
        $this->list = $list;
        $this->getDispatcher()?->dispatch(new TableEvent($this), TableEvents::TABLE_INIT);

        return $this;
    }

    /**
     * Get the data list
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * Execute any Table and cell request responses
     */
    public function execute(Request $request)
    {
        foreach ($this->getCells() as $cell) {
            $cell->execute($request);
        }

        $this->getDispatcher()?->dispatch(new TableEvent($this), TableEvents::TABLE_EXECUTE);
    }



    public function getDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * The id can only be set once unless it is cleared first
     */
    protected function setId($id): static
    {
        static $instances = [];
        if ($this->id) return $this;
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

    /**
     * Get the table id
     */
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


    public function setCells(Collection $cells): static
    {
        foreach ($cells as $cell) {
            $cell->setTable($this);
        }
        $this->cells = $cells;
        return $this;
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

}