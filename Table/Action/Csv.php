<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * For this action to work the object must contain a delete() method
 *
 *
 * @package Table\Action
 * @note: Work out a way to run the sql query again without any limits so we get all records in the CSV
 * @note: We need the contents of the table not the contents of the object, exacly what is rendered to the table cells
 */
class Csv extends Iface
{

    protected $confirmMsg = 'Are you sure you want to export the table records.';

    protected $ignore = array('cb', 'action', 'actions');

    /**
     * Create a delete action
     *
     * @return \Table\Action\Csv
     */
    static function create()
    {
        $obj = new self('csv', \Tk\Request::getInstance()->getRequestUri());
        $obj->addClass('fa fa-table');
        $obj->setLabel('CSV Export');
        return $obj;
    }

    /**
     * setConfirm
     *
     * @param string $str
     * @return \Table\Action\Delete
     */
    public function setConfirm($str)
    {
        $this->confirmMsg = $str;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @param \Tk\Db\ArrayObject $list
     * @throws \Tk\Exception
     * @see \Table\Action\Iface::execute()
     */
    public function execute($list)
    {
        // Headers for an download:
        ini_set('max_execution_time', 0);
        $file = 'table.csv';
        if ($this->getRequest()->exists('fname')) {
            $file = preg_replace('/[^a-z0-9_\.-]/i', '_', basename(strip_tags(trim($this->getRequest()->exists('fname')))));
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');
        // TODO: add header names....
        $out = fopen('php://output', 'w');

        // Get list with no limit...
        $class = get_class($list->current());
        if (!$class)
            throw new \Tk\Exception('Cannot create empty csv.');
        $sql = $this->getTable()->lastSqlQuery;
        if (preg_match('/ LIMIT /i', $sql)) {
            $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
        }
        $result = $this->getConfig()->getDb()->query($sql);
        $tool = clone $list->getTool();
        $tool->setLimit(0)->setOffset(0);
        $mapper = $this->table->getList()->getMapper();
        $fullList = null;
        if ($mapper && $result->rowCount() < 10000) {   // May halt the system if using memory, see how this works anyway
            $fullList = $mapper->makeCollection($result, $tool);
        }


        $arr = array();
        // Write cell labels to first line of csv...
        foreach ($this->table->getCellList() as $i => $cell) {
            if (in_array($cell->getProperty(), $this->ignore)) continue;
            $arr[] = $cell->getLabel();
        }
        fputcsv($out, $arr);

        if ($fullList) {
            foreach ($fullList as $obj) {
                $arr = array();
                /* @var $cell \Table\Cell\Iface */
                foreach ($this->table->getCellList() as $cell) {
                    if (in_array($cell->getProperty(), $this->ignore)) continue;
                    $arr[$cell->getLabel()] = $cell->getCsv($obj);
                }
                fputcsv($out, $arr);
            }
        } else {
            foreach($result as $obj) {
                $arr = array();
                /* @var $cell \Table\Cell\Iface */
                foreach ($this->table->getCellList() as $i => $cell) {
                    if (in_array($cell->getProperty(), $this->ignore)) continue;
                    $arr[$cell->getLabel()] = $cell->getCsv($obj);
                }
                fputcsv($out, $arr);
            }
        }
        fclose($out);
        exit;
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\Template You can also return HTML string
     */
    public function getHtml($list)
    {
        $js = sprintf("$(this).unbind('click'); return confirm('%s');", $this->confirmMsg);
        $url = $this->getUri()->set($this->getObjectKey('csv'));
        return sprintf('<a class="btn btn-default btn-xs" href="%s" onclick="%s" title="%s"><span class="%s"></span> %s</a>',
            htmlentities($url->toString()), $js, $this->notes, $this->getClassString(), $this->label);
    }


}