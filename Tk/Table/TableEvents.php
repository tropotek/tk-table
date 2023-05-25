<?php
namespace Tk\Table;

class TableEvents
{

    /**
     * @event \Tk\Event\TableEvent
     */
    const TABLE_INIT = 'table.init';

    /**
     * @event \Tk\Event\TableEvent
     */
    const TABLE_EXECUTE = 'table.execute';

}