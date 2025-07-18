<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;
use Tk\Db\Model;
use Tk\Table\Exception;
use Tk\Uri;
use Tk\Table\Cell;
use Tk\Table\Cell\OrderBy;
use Tk\Table\Cell\RowSelect;

/**
 * Add a CSV export action.
 * Example:
 * ```
 *     $this->appendAction(Csv::create()
 *         ->addOnExecute(function(Csv $action) use ($rowSelect) {
 *             $action->setExcluded(['actions', 'permissions']);
 *             if (!$this->getCell(\App\Db\File::getPrimaryProperty())) {
 *                 $this->prependCell(\App\Db\File::getPrimaryProperty())->setHeader('id');
 *             }
 *             $selected = $rowSelect->getSelected();
 *             $filter = $this->getDbFilter();
 *             if (count($selected)) {
 *                 $filter['fileId'] = $selected;
 *                 $rows = \App\Db\File::findFiltered($filter);
 *             } else {
 *                 $rows = \App\Db\File::findFiltered($filter->resetLimits());
 *             }
 *             return $rows;
 *         })
 *     );
 * ```
 */
class Csv extends Button
{
    const array EXCLUDED_CELLS = [
        OrderBy::class,
        RowSelect::class,
    ];

    protected string    $filename = '';
    protected array     $excluded = [];


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setAttr('title', 'Export Records');
        $this->addCss('btn btn-sm btn-light tk-action-csv');
        $this->removeAttr('disabled');
        $this->addExcluded([OrderBy::class, RowSelect::class, 'actions']);
    }

    public static function create(string $name = 'export', string $icon = 'far fa-fw fa-list-alt'): self
    {
        $obj = new self($name);
        $obj->icon = $icon;
        return $obj;
    }

    public static function createDefault(string $class, ?RowSelect $rowSelect = null, array $filterExtras = []): self
    {
        if (!in_array(Model::class, class_parents($class))) {
            throw new Exception("class must be a Db Model");
        }

        $obj = new self('export');
        $obj->addOnExecute(function(Csv $action) use ($class, $rowSelect, $filterExtras) {
            if (!$action->table->getCell($class::getPrimaryProperty())) {
                $action->table->prependCell($class::getPrimaryProperty())->setHeader('id');
            }

            $filter = $action->table->getDbFilter()->resetLimits();
            $filter->replace($filterExtras);
            if ($rowSelect instanceof RowSelect) {
                $selected = $rowSelect->getSelected();
                $filter->set($class::getPrimaryProperty(), $selected);
                $rows = $class::findFiltered($filter);
            } else {
                $rows = $class::findFiltered($filter);
            }
            return $rows;
        });
        return $obj;
    }

    public function execute(): void
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(isset($_POST[$selectName]));
        if (!$this->isActive()) return;

        $rows = $this->getOnExecute()->execute($this);
        if (!count($rows)) {
            Uri::create()->redirect();
        }

        $filename = $this->getTable()->getId() . '_' . date('Ymd') . '.csv';
        if ($this->getFilename()) {
            $filename = $this->getFilename() . '_' . date('Ymd') . '.csv';
        }

        // Output the CSV data
        $out = fopen('php://output', 'w');
        if ($out === false) {
            throw new Exception("failed to open output stream");
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');

        $arr = [];
        // Write cell labels to first line of csv...
        /* @var $cell Cell */
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isExcluded($cell)) continue;
            $arr[] = $cell->getHeader();
        }
        fputcsv($out, $arr);

        foreach ($rows as $i => $row) {
            $csvData = [];
            /* @var $cell Cell */
            foreach ($this->getTable()->getCells() as $cell) {
                if ($this->isExcluded($cell)) continue;
                $csvData[$cell->getName()] = $cell->getValue($row);
            }
            fputcsv($out, $csvData);
        }

        fclose($out);
        exit;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     * @deprecated use addOnExecute()
     */
    public function addOnCsv(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnExecute()->append($callable, $priority);
        return $this;
    }

    public function getExcluded(): array
    {
        return $this->excluded;
    }

    /**
     * An array of cell names to exclude from the CSV data
     */
    public function setExcluded(array $excluded): static
    {
        $this->excluded = $excluded;
        return $this;
    }

    /**
     * Add to the cell exclude list
     */
    public function addExcluded(string|array $excluded): static
    {
        if (is_string($excluded)) $excluded = [$excluded];
        $this->excluded = array_merge($this->excluded, $excluded);
        return $this;
    }

    private function isExcluded(Cell $cell): bool
    {
        if (in_array(get_class($cell), self::EXCLUDED_CELLS)) return true;
        return in_array($cell->getName(), $this->excluded);
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

}