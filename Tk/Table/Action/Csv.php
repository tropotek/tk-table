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
class Csv extends Iface
{


    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * Create
     *
     * @param string $name
     * @param string $checkboxName
     */
    public function __construct($name = 'csv', $checkboxName = 'id')
    {
        parent::__construct($name);
        $this->checkboxName = $checkboxName;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->makeInstanceKey($this->getName())])) {
            return;
        }

        // Headers for an download:
        ini_set('max_execution_time', 0);


        //TODO: Choose what one is better
        //$uri = \Tk\Url::create();
        //$file = trim(basename($uri->getPath()), '.php') . '_' . date('Ymd') . '.csv';
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
        } else if ($list->getFoundRows() > $list->count()) {
            $st = $list->getStatement();
            $sql = $st->queryString;
            if (preg_match('/ LIMIT /i', $sql)) {
                $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
            }

            $stmt = \Tk\Config::getInstance()->getDb()->prepare($sql);
            $stmt->execute($st->getBindParams());
            $fullList = \Tk\Db\ArrayObject::createFromMapper($list->getMapper(), $stmt);
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

        foreach ($fullList as $obj) {
            $arr = array();
            /* @var $cell Cell\Iface */
            foreach ($this->table->getCellList() as $cell) {
                //if (in_array($cell->getProperty(), $this->ignore)) continue;
                $arr[$cell->getLabel()] = $cell->getCellCsv($obj);
            }
            fputcsv($out, $arr);
        }

        fclose($out);
        exit;
    }



    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $xhtml = <<<XHTML
 <button type="submit" class="btn btn-xs" title="Export records as a CSV file." var="btn"><i class="glyphicon glyphicon-export"></i> </button>
XHTML;
        $template = \Dom\Loader::load($xhtml);


        $template->appendHtml('btn', $this->getLabel());

        $btnId = $this->makeInstanceKey($this->getName());
        $template->setAttr('btn', 'id', 'fid-'.$btnId);
        $template->setAttr('btn', 'name', $btnId);
        $template->setAttr('btn', 'value', $btnId);

        // Element css class names
        foreach($this->getCssList() as $v) {
            $template->addClass('btn', $v);
        }

        $js = <<<JS
jQuery(function($) {
  $('#fid-$btnId').on('click', function (e) {
      return confirm('Are you sure you want to export the table records.');
  });
});
JS;
        //$template->appendJs($js);

        return $template;
    }


}
