<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Tree;

/**
 *  This interface should be implemented in objects that
 *  use the Table\Tree\Renderer object
 *
 * @package Table
 */
interface Iface
{
    
    /**
     * Get any children objects
     * Return an empty array if no children are defined
     *
     * @param \Tk\Db\Tool $tool
     * @return array
     */
    public function getChildren($tool = null);
    
    /**
     * Get the parent of this object if available
     * Return null if no parent available
     *
     * @return \Table\Tree\Iface
     */
    public function getParent();

}
