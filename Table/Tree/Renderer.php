<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Tree;

/**
 * The dynamic form renderer
 *
 *
 * @package Table\Tree
 */
class Renderer extends \Table\Renderer
{
    private $idx = 0;

    
        
    /**
     * Render the table data rows
     *
     * @param array $list
     * @param int $nest
     */
    public function showTd($list, $nest = 0)
    {
        $template = $this->getTemplate();
        
        /* @var $obj \Tk\Object */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            
            $rowClassArr = $this->insertRow($obj, $repeatRow);
            
            $rowClass = 'r_' . $this->idx . ' ' . $repeatRow->getAttr('tr', 'class') . ' ';
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ', $rowClassArr);
            }
            if ($nest >= 1) {
                $rowClass .= ' nest';
            }
            $rowClass = trim($rowClass);
            
            if ($this->idx % 2) {
                $repeatRow->setAttr('tr', 'class', 'odd ' . $rowClass);
            } else {
                $repeatRow->setAttr('tr', 'class', 'even ' . $rowClass);
            }
            $this->idx++;
            $repeatRow->appendRepeat();
            
            $children = $obj->getChildren($this->getList()->getTool());
            if ($children) {
                $this->showTd($children, $nest + 1);
            }
        }
    }
    
    /**
     * Insert an object's cells into a row
     *
     * @param \Tk\Object $obj
     * @param \Dom\Template $template The row repeat template
     * @return array
     */
    protected function insertRow($obj, $template)
    {
        $rowClassArr = array();
        /* @var $cell \Table\Cell */
        foreach ($this->table->getCellList() as $i => $cell) {
            $repeat = $template->getRepeat('td');
            
            $class = '';
            $class .= 'm' . ucfirst($cell->getProperty()) . ' ';
            if (count($cell->getClassList())) {
                $class = implode(' ', $cell->getClassList()) . ' ';
            }
            if ($cell->isKey()) {
                $class .= 'key ';
            }
            //$class .= $cell->getAlign() . ' ';
            $class = trim($repeat->getAttr('td', 'class') . ' ' . $class);
            $repeat->setAttr('td', 'class', $class);
            $repeat->setAttr('td', 'title', $cell->getLabel());
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());
            
            $data = $cell->getTd($obj);
            if ($data === null) {
                $data = '&#160;';
            }
            if ($data instanceof \Dom\Template) {
                $repeat->insertTemplate('td', $data);
            } else {
                $repeat->insertHTML('td', $data);
            }
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }
    
}