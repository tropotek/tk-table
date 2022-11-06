<?php

namespace Tk\Table;

use Tk\Collection;
use Tk\Db\Tool;
use Tk\Factory;
use Tk\InstanceKey;
use Tk\Table\Cell\CellInterface;
use Tk\Traits\SystemTrait;
use Tk\Uri;


class TableSession extends Collection implements InstanceKey
{

    protected string $tableId;

    protected int $offset = 0;

    protected ?int $limit = null;

    protected ?string $orderBy = null;

    /**
     * Total rows available from the source.
     * This value is all possible results available without paging offset.
     */
    protected int $rowTotal = 0;


    public function __construct(string $tableId)
    {
        $this->tableId = $tableId;
    }

    public function getTableId(): string
    {
        return $this->tableId;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): TableSession
    {
        $this->offset = $offset;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit ?? 0;
    }

    public function setLimit(int $limit): TableSession
    {
        $this->limit = $limit;
        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy ?? '';
    }

    public function setOrderBy(string $orderBy): TableSession
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getRowTotal(): int
    {
        return $this->rowTotal;
    }

    public function setRowTotal(int $rowTotal): TableSession
    {
        $this->rowTotal = $rowTotal;
        return $this;
    }

    /**
     * Get the active order By direction
     * Will be one of: '', 'ASC', 'DESC'
     */
    public function getOrderByDir(): string
    {
        $ord = $this->getOrderByParts();
        return $ord[1] ?? CellInterface::ORDER_NONE;
    }

    /**
     * Get the active order cell name
     */
    public function getOrderByName(): string
    {
        $ord = $this->getOrderByParts();
        return $ord[0] ?? '';
    }

    /**
     * Get the cell name and order value from the current order by
     * EG: from "lastName DESC" TO array('lastName', 'DESC');
     */
    private function getOrderByParts(): array
    {
        return explode(' ', $this->getOrderBy());
    }

    public function replace(array $all): static
    {
        if (isset($all[$this->makeInstanceKey(Tool::PARAM_ORDER_BY)])) {
            $this->setOrderBy($all[$this->makeInstanceKey(Tool::PARAM_ORDER_BY)]);
        }
        if (isset($all[$this->makeInstanceKey(Tool::PARAM_LIMIT)])) {
            $this->setLimit($all[$this->makeInstanceKey(Tool::PARAM_LIMIT)]);
        }
        if (isset($all[$this->makeInstanceKey(Tool::PARAM_OFFSET)])) {
            $this->setOffset($all[$this->makeInstanceKey(Tool::PARAM_OFFSET)]);
        }
        return $this;
    }

    public function all($prefix = ''): array
    {
        return [
            $prefix.Tool::PARAM_ORDER_BY => $this->getOrderBy(),
            $prefix.Tool::PARAM_LIMIT    => $this->getLimit(),
            $prefix.Tool::PARAM_OFFSET   => $this->getOffset(),
        ];
    }

    public function makeInstanceKey(string $key): string
    {
        return $this->getTableId() . '-' . $key;
    }

    public function getTool(string $defaultOrderBy = '', int $defaultLimit = 0): Tool
    {
        $tool = Tool::create($defaultOrderBy, $defaultLimit);
        $tool->setInstanceId($this->getTableId());

        if ($this->orderBy === null && $defaultOrderBy) $this->setOrderBy($defaultOrderBy);
        if ($this->limit === null && $defaultLimit) $this->setLimit($defaultLimit);

        $tool->updateFromArray($this->all($tool->makeInstanceKey('')));

        $request = Factory::instance()->getRequest();
        $updated = $tool->updateFromArray($request->query->all());

        // Redirect on update
        if ($updated) {
            $this->replace($tool->toArray());
            Uri::create()
                ->remove($this->makeInstanceKey(Tool::PARAM_ORDER_BY))
                ->remove($this->makeInstanceKey(Tool::PARAM_LIMIT))
                ->remove($this->makeInstanceKey(Tool::PARAM_OFFSET))
                ->redirect();
        }

        return $tool;
    }

}