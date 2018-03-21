<?php
namespace Tk\Table;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
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