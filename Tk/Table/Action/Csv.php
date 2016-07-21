<?php
namespace Tk\Table\Action;

use \Tk\Table\Cell;

/**
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Csv extends Button
{
    
    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * Create
     *
     * @param \Tk\Db\Pdo $db
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     */
    public function __construct($db, $name = 'csv', $checkboxName = 'id', $icon = 'glyphicon glyphicon-list-alt')
    {
        parent::__construct($name, $icon);
        $this->db = $db;
        $this->checkboxName = $checkboxName;
    }

    /**
     * Create
     * 
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return Csv
     */
    static function getInstance($db, $name = 'csv', $checkboxName = 'id', $icon = 'glyphicon glyphicon-list-alt')
    {
        return new self($db, $name, $checkboxName, $icon);
    }
    
    
    /**
     * @return mixed
     */
    public function execute()
    {   
        $request = $this->getTable()->getRequest();
        // Headers for an download:
        ini_set('max_execution_time', 0);

        $file = $this->getTable()->getId() . '_' . date('Ymd') . '.csv';
        if (isset($request['csv_name'])) {
            $file = preg_replace('/[^a-z0-9_\.-]/i', '_', basename(strip_tags(trim($request['csv_name']))));
        }

        // Get list with no limit...
        $list = $this->getTable()->getList();

        $fullList = $list;
        if (isset($request[$this->checkboxName]) && is_array($request[$this->checkboxName])) {
            $fullList = array();

            //TODO: Choose what one is better
            foreach($list as $obj) {
                if (in_array($obj->getId(), $request[$this->checkboxName])) {
                    $fullList[] = $obj;
                }
            }
        } else if ($list && $list->getFoundRows() > $list->count()) {
            $st = $list->getStatement();
            $sql = $st->queryString;
            if (preg_match('/ LIMIT /i', $sql)) {
                $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($st->getBindParams());
            $fullList = \Tk\Db\Map\ArrayObject::createFromMapper($list->getMapper(), $stmt);
        }

        // Output the CSV data
        $out = fopen('php://output', 'w');

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');

        $arr = array();
        // Write cell labels to first line of csv...
        foreach ($this->table->getCellList() as $i => $cell) {
            //if (in_array($cell->getProperty(), $this->ignore)) continue;
            $arr[] = $cell->getLabel();
        }

        fputcsv($out, $arr);
        if ($fullList) {
            foreach ($fullList as $obj) {
                $arr = array();
                /* @var $cell Cell\Iface */
                foreach ($this->table->getCellList() as $cell) {
                    //if (in_array($cell->getProperty(), $this->ignore)) continue;
                    $arr[$cell->getLabel()] = $cell->getCellCsv($obj);
                }
                fputcsv($out, $arr);
            }
        }

        fclose($out);
        exit;
    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $template = parent::getHtml();

        $template->setAttr('var', 'title', 'Export records as a CSV file.');
        $template->addClass('var', 'disabled');

        return $template;
    }


}
