<?php

namespace Tk\Table\Ui;

use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\Table;
use Tk\Ui\DomElement;

abstract class UiInterface extends DomElement
{

    const PARAM_LIMIT = 'limit';
    const PARAM_OFFSET = 'offset';
    const PARAM_TOTAL = 'total';

    const CSS_SELECTED = 'active';
    const CSS_DISABLED = 'disabled';


    protected Table $table;

    protected bool $enabled = true;


    public function initFromResult(Result $list): static
    {
        return $this;
    }

    public function initFromDbTool(Tool $tool): static
    {
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setTable(Table $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Create request keys with prepended string
     * returns: `{talbeId}-{$key}`
     */
    public function makeInstanceKey($key): string
    {
        if ($this->getTable()) {
            return $this->getTable()->getId() . '-' . $key;
        }
        return $key;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): UiInterface
    {
        $this->enabled = $enabled;
        return $this;
    }

}