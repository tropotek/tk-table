<?php
namespace Tk\Table\Action;

use Dom\Template;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;
use Tk\Db\Mapper\Model;
use Tk\Db\Mapper\Result;
use Tk\Db\Pdo;
use Tk\ObjectUtil;
use Tk\Table;
use \Tk\Table\Cell;

class Csv extends Button
{

    protected Pdo $db;

    protected string $checkboxName = 'id';

    protected string $filename = '';

    protected array $excluded = ['actions'];

    protected array $excludedClasses = [
        Cell\RowSelect::class,
        Cell\OrderBy::class,
    ];


    public function __construct(string $name = 'csv', string $checkboxName = 'id', string $icon = 'fa fa-list-alt')
    {
        $this->db = $this->getFactory()->getDb();
        parent::__construct($name, $icon);
        $this->setCheckboxName($checkboxName);
        $this->addCss('tk-action-csv no-loader');
    }

    public function execute(Request $request): void
    {
        parent::execute($request);
        if (!$this->isTriggered()) return;

        $this->doCsv($request);
    }

    #[NoReturn] public function doCsv(Request $request): void
    {
        ini_set('max_execution_time', 0);

        $file = $this->getTable()->getId() . '_' . date('Ymd') . '.csv';
        if ($this->getFilename()) {
            $file = $this->getFilename() . '_' . date('Ymd') . '.csv';
        }

        /** @var Table\Cell\RowSelect $checkbox */
        $checkbox = $this->getTable()->getCell($this->getCheckboxName());

        // Get list with no limit...
        $list = $this->getTable()->getList();
        $fullList = $list;
        if (count($checkbox->getSelected())) {  // Only export selected rows
            $fullList = [];
            foreach($list as $obj) {
                if (is_array($obj)) {
                    $keyValue = $obj[$this->getCheckboxName()] ?? '';
                } else {
                    $keyValue = ObjectUtil::getPropertyValue($obj, $this->getCheckboxName());
                }
                if ($keyValue && $checkbox->isSelected($keyValue)) {
                    $fullList[] = $obj;
                }
            }
        } else { // Export all rows
            if (is_array($list)) {
                $sql = $this->getDb()->getLastQuery();
                if (preg_match('/ LIMIT /i', $sql)) {
                    $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
                }
                $stmt = $this->getDb()->prepare($sql);
                $stmt->execute();
                //$fullList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $fullList = $stmt->fetchAll();
            } else if ($list instanceof Result) {
                $st = $list->getStatement();
                $sql = $st->queryString;
                if (preg_match('/ LIMIT /i', $sql)) {
                    $sql = substr($sql, 0, strrpos($sql, 'LIMIT'));
                }

                $stmt = $this->getDb()->prepare($sql);
                $stmt->execute($st->getBindParams() ?? []);
                if ($list->getMapper()) {
                    $fullList = Result::createFromMapper($list->getMapper(), $stmt);
                } else {
                    $fullList = Result::create($stmt);
                }
            }
        }

        // Output the CSV data
        $out = fopen('php://output', 'w');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');

        $arr = [];
        // Write cell labels to first line of csv...
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isExcluded($cell)) continue;
            $arr[] = $cell->getname();
        }
        fputcsv($out, $arr);
        if ($fullList) {
            foreach ($fullList as $i => $rowData) {
                $row = Table\Row::createRow($this->getTable(), $rowData, $i+1);
                $csvData = [];
                /* @var $cell Cell\CellInterface */
                foreach ($row->getCells() as $cell) {
                    if ($this->isExcluded($cell)) continue;
                    $csvData[$cell->getLabel()] = $cell->getCellValue();
                }
                fputcsv($out, $csvData);
            }
        }

        fclose($out);
        exit;
    }

    public function show(): ?Template
    {
        $this->setAttr('title', 'Export records as a CSV file.');
        return parent::show();
    }

    public function getCheckboxName(): string
    {
        return $this->checkboxName;
    }

    public function setCheckboxName(string $checkboxName): static
    {
        $this->checkboxName = $checkboxName;
        return $this;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    private function isExcluded(Cell\CellInterface $cell): bool
    {
        if (in_array(get_class($cell), $this->excludedClasses)) return true;
        return in_array($cell->getName(), $this->excluded);
    }

    public function addExcluded(string $cellName): static
    {
        $this->excluded[] = $cellName;
        return $this;
    }

    /**
     * The excluded cell class array or reset the array if nothing passed
     * Eg:  array('cellName1', 'cellName2');
     */
    public function setExcluded(array $array = []): static
    {
        $this->excluded = $array;
        return $this;
    }

}
