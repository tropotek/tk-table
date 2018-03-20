<?php
namespace Tk\Table\Action;

use \Tk\Table\Cell;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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


    protected $ignoreCellList = array(
        'Tk\Table\Cell\Checkbox'
    );


    /**
     * Create
     *
     * @param \Tk\Db\Pdo $db
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @throws \Tk\Exception
     */
    public function __construct($db, $name = 'csv', $checkboxName = 'id', $icon = 'glyphicon glyphicon-list-alt')
    {
        parent::__construct($name, $icon);
        $this->db = $db;
        $this->checkboxName = $checkboxName;
        $this->addCss('tk-action-csv');
    }

    /**
     * Create
     *
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @param \Tk\Db\Pdo $db
     * @return Csv
     * @throws \Tk\Exception
     */
    static function create($name = 'csv', $checkboxName = 'id', $icon = 'glyphicon glyphicon-list-alt', $db = null)
    {
        if ($db === null)
            $db = \Tk\Config::getInstance()->getDb();

        return new self($db, $name, $checkboxName, $icon);
    }

    /**
     * @return mixed|void
     * @throws \Tk\Db\Exception
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
            foreach($list as $obj) {
                if (in_array($obj->getId(), $request[$this->checkboxName])) {
                    $fullList[] = $obj;
                }
            }
        } else if ($list && is_object($list) && $list->countAll() > $list->count()) {
            $st = $list->getStatement();
            $sql = $st->queryString;
            if (preg_match('/ LIMIT /i', $sql)) {
                $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($st->getBindParams());
            if ($list->getMapper()) {
                $fullList = \Tk\Db\Map\ArrayObject::createFromMapper($list->getMapper(), $stmt);
            } else {
                $fullList = \Tk\Db\Map\ArrayObject::create($stmt);
            }
        }

        // Output the CSV data
        $out = fopen('php://output', 'w');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');

        $arr = array();
        // Write cell labels to first line of csv...
        foreach ($this->table->getCellList() as $i => $cell) {
            if ($this->ignoreCell($cell)) continue;
            $arr[] = $cell->getLabel();
        }

        fputcsv($out, $arr);
        if ($fullList) {
            foreach ($fullList as $obj) {
                $arr = array();
                /* @var $cell Cell\Iface */
                foreach ($this->table->getCellList() as $cell) {
                    if ($this->ignoreCell($cell)) continue;
                    $arr[$cell->getLabel()] = $cell->getRawValue($obj);
                }
                fputcsv($out, $arr);
            }
        }

        fclose($out);
        exit;
    }

    /**
     * @return string|\Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $this->setAttr('title', 'Export records as a CSV file.');

        $template = parent::show();

        return $template;
    }

    /**
     *
     * @param \Tk\Table\Cell\Iface $cell
     * @return array
     */
    private function ignoreCell($cell)
    {
        return in_array(get_class($cell), $this->ignoreCellList);
    }

    /**
     *
     * @param \Tk\Table\Cell\Iface $cell
     * @return $this
     */
    public function addIgnoreCell($cell)
    {
        $this->ignoreCellList[get_class($cell)] = get_class($cell);
        return $this;
    }

    /**
     * Set the ignore cell class array or reset the array if nothing passed
     *
     * Eg:
     *   array('Tk\Table\Cell\Checkbox', 'App\Ui\Subject\EnrolledCell');
     *
     * @param array $array
     * @return $this
     */
    public function setIgnoreCellList($array = array())
    {
        $this->ignoreCellList = $array;
        return $this;
    }

}
