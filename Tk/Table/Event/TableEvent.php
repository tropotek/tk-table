<?php
namespace Tk\Table\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tk\Table;

class TableEvent extends Event
{

    protected Table $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

}