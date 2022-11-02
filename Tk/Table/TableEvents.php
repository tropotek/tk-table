<?php
namespace Tk\Table;


/**
 * @author Tropotek <http://www.tropotek.com/>
 */
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