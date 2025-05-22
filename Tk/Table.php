<?php
namespace Tk;

use Tk\Db\Session;
use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Table\Action;
use Tk\Table\Cell;

class Table
{
    use AttributesTrait;

    const string PARAM_LIMIT    = 'limit';
    const string PARAM_OFFSET   = 'offset';
    const string PARAM_PAGE     = 'page';
    const string PARAM_TOTAL    = 'total';
    const string PARAM_ORDERBY  = 'orderBy';

    protected string     $id        = '';
    protected int        $limit     = 0;
    protected int        $page      = 1;
    protected string     $orderBy   = '';
    protected int        $totalRows = 0;

    /** @var array<int|string, mixed> */
    protected array      $rows      = [];

    protected Collection $cells;
    protected Collection $actions;
    protected Attributes $rowAttrs;
    protected Attributes $headerAttrs;


    public function __construct(string $tableId = 'tbl')
    {
        $this->rowAttrs    = new Attributes();
        $this->headerAttrs = new Attributes();
        $this->cells       = new Collection();
        $this->actions     = new Collection();
        $this->setId($tableId);
    }

    public function getSessionId(): string
    {
        return "tbl_{$this->getId()}";
    }

    public function resetTableSession(): static
    {
        Session::remove($this->getSessionId());
        return $this;
    }

    public function getTableSession(): Collection
    {
        $session = Session::get($this->getSessionId());
        if (is_null($session)) {
            $session = new Collection();
        }
        Session::set($this->getSessionId(), $session, 60*10);
        return $session;
    }

    /**
     * manage all pager properties
     * checks if the is values in the session uses them first
     * then checks the request query for any pager values to update
     * Saves new values to session
     * redirects if a change has occurred
     */
    protected function initPager(): void
    {
        $kLimit   = $this->makeRequestKey(self::PARAM_LIMIT);
        $kPage    = $this->makeRequestKey(self::PARAM_PAGE);
        $kOrderBy = $this->makeRequestKey(self::PARAM_ORDERBY);
        $ses      = $this->getTableSession();
        $reload   = false;

        // first check session for vals
        $this->setLimit($ses->get($kLimit, $this->getLimit()));
        $this->setPage($ses->get($kPage, $this->getPage()));
        $this->setOrderBy($ses->get($kOrderBy, $this->getOrderBy()));

        // Second check request for any changes and redirect removing query params if found
        if (isset($_GET[$kPage])) {
            $this->setPage((int)$_GET[$kPage]);
            $reload = true;
        }
        if (isset($_GET[$kOrderBy])) {
            $this->setOrderBy(trim($_GET[$kOrderBy]));
            $reload = true;
        }
        if (isset($_GET[$kLimit])) {
            $this->setLimit((int)$_GET[$kLimit]);
            $this->setPage(1);
            $reload = true;
        }

        // save session
        $ses->set($kLimit, $this->getLimit())->set($kPage, $this->getPage())->set($kOrderBy, $this->getOrderBy());

        if ($reload) {
            Uri::create()->remove($kLimit)->remove($kPage)->remove($kOrderBy)->redirect();
        }
    }

    /**
     * Execute table actions, should be called after all cells, filters and actions are added to the table
     */
    public function execute(): static
    {
        $this->initPager();

        /* @var Cell $action */
        foreach ($this->getCells() as $cells) {
            $cells->execute();
        }

        /* @var Action $action */
        foreach ($this->getActions() as $action) {
            $action->execute();
        }

        return $this;
    }

    /**
     * ensure the id is unique
     */
    protected function setId(string $id): static
    {
        static $instances = [];
        if ($this->getId()) return $this;
        if (isset($instances[$id])) {
            $instances[$id]++;
        } else {
            $instances[$id] = 0;
        }
        if ($instances[$id] > 0) $id = $instances[$id].$id;
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): Table
    {
        $this->orderBy = preg_replace("/(\r|\n)/", '', $orderBy);
        return $this;
    }

    public function getRowAttrs(): Attributes
    {
        return $this->rowAttrs;
    }

    public function setRowAttrs(Attributes $rowAttrs): Table
    {
        $this->rowAttrs = $rowAttrs;
        return $this;
    }

    public function getHeaderAttrs(): Attributes
    {
        return $this->headerAttrs;
    }

    /**
     * returns the total found rows if supplied with Table::setRows()
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * @return null|array<int|string, mixed>
     */
    public function getRows(): ?array
    {
        return $this->rows;
    }

    /**
     * @param array<int|string, mixed> $rows
     */
    public function setRows(array $rows, ?int $totalRows = null): static
    {
        $this->rows = $rows;
        $this->totalRows = is_null($totalRows) ? count($rows) : $totalRows;
        return $this;
    }

    /**
     * return the total rows for this page
     */
    public function getRowCount(): int
    {
        return count($this->getRows());
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): Table
    {
        $this->limit = ($limit < 0) ? 0 : $limit;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): Table
    {
        $this->page = ($page < 1) ? 1 : $page;
        return $this;
    }

    public function getOffset(): int
    {
        return $this->getLimit() * ($this->getPage()-1);
    }

    public function getCells(): Collection
    {
        return $this->cells;
    }

    public function getCell(string $name): ?Cell
    {
        return $this->getCells()->get($name);
    }

    public function removeCell(string $cellName): static
    {
        $this->getCells()->remove($cellName);
        return $this;
    }

    public function appendCell(string|Cell $cell, ?string $after = null): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCells()->has($cell->getName())) {
            throw new \Tk\Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->append($cell->getName(), $cell, $after);
    }

    public function prependCell(string|Cell $cell, ?string $before = null): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCells()->has($cell->getName())) {
            throw new \Tk\Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->prepend($cell->getName(), $cell, $before);
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getAction(string $name): ?Action
    {
        return $this->getActions()->get($name);
    }

    public function removeAction(string $actionName): static
    {
        $this->getActions()->remove($actionName);
        return $this;
    }

    public function appendAction(string|Action $action, ?string $after = null): Action
    {
        if (is_string($action)) {
            $action = new Action($action);
        }
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->append($action->getName(), $action, $after);
    }

    public function prependAction(string|Action $action, ?string $before = null): Action
    {
        if (is_string($action)) {
            $action = new Action($action);
        }
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->prepend($action->getName(), $action, $before);
    }

    /**
     * Create request key with prepended string
     * returns: `{id}_{$key}`
     */
    public function makeRequestKey(string $key): string
    {
        return $this->getId() . '_' . $key;
    }

}