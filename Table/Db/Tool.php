<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Db;

/**
 * The TOOL object is named from the params (Total, Offset, OrderBy, Limit)
 *
 * This object manages a query's params $orderBy, $limit, $offset and $total
 * where total is the total number of records available without a limit.
 *
 * Useful for persistent storage of table data and record positions
 *
 * @package Table
 */
class Tool extends \Tk\Db\Tool 
{


    /**
     * Create a listParams object from a request object
     *
     * @param int $instanceId This is used to create the unique request key
     * @param string $orderBy The default orderby to use
     * @param int $limit
     * @return \Tk\Db\Tool
     */
    static function createFromRequest($instanceId = null, $orderBy = '', $limit = 50)
    {
        $tool = new self($orderBy, $limit, 0);
        $tool->setInstanceId($instanceId);
        //$tool->getSession()->delete($tool->getSessionHash());
        if ($tool->getSession()->exists($tool->getSessionHash())) {
            $tool = $tool->getSession()->get($tool->getSessionHash());
        }

        $request = $tool->getRequest();
        if ($request->exists($tool->getObjectKey(self::REQ_OFFSET))) {
            $tool->setOffset($request->get($tool->getObjectKey(self::REQ_OFFSET)));
        }
        if ($request->exists($tool->getObjectKey(self::REQ_LIMIT))) {
            $tool->setLimit($request->get($tool->getObjectKey(self::REQ_LIMIT)));
            $tool->setOffset(0);
        }
        if ($request->exists($tool->getObjectKey(self::REQ_ORDER_BY))) {
            $tool->setOrderBy($request->get($tool->getObjectKey(self::REQ_ORDER_BY)));
            $tool->setOffset(0);
        }
        return $tool;
    }




}
