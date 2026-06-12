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

    protected string     $filename = '';
    /** @var list<string> */
    protected array      $excluded = [];

    protected string     $class = '';
    protected ?RowSelect $rowSelect = null;
    protected array      $filterExtras = [];
    protected array      $rows = [];


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

    /**
     * @param array<string, string> $filterExtras
     */
    public static function createDefault(string $class, ?RowSelect $rowSelect = null, array $filterExtras = []): self
    {
        $parents = class_parents($class);
        if (!(is_array($parents) && in_array(Model::class, $parents))) {
            throw new Exception("class must be a Db Model");
        }

        $obj = new self('export');
        $obj->class = $class;
        $obj->rowSelect = $rowSelect;
        $obj->filterExtras = $filterExtras;

        $obj->addOnExecute(function(Csv $action) use ($class, $rowSelect, $filterExtras) {
            if (!$action->getTable()->getCell($class::getPrimaryProperty())) {
                $action->getTable()->prependCell($class::getPrimaryProperty())->setHeader('id');
            }
        });
        return $obj;
    }

    public function execute(): void
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(isset($_POST[$selectName]));
        if (!$this->isActive()) return;

        // @phpstan-ignore-next-line
        $filter = $this->getTable()->getDbFilter()->resetLimits();
        $filter->replace($this->filterExtras);
        if ($this->rowSelect instanceof RowSelect) {
            $selected = $this->rowSelect->getSelected();
            $filter->set($this->class::getPrimaryProperty(), $selected);
            $this->rows = $this->class::findFiltered($filter);
        } else {
            $this->rows = $this->class::findFiltered($filter);
        }

        $this->getOnExecute()->execute($this);
        if (!count($this->rows)) {
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
        fputcsv(
            stream: $out,
            fields: $arr,
            escape: ''
        );

        foreach ($this->rows as $i => $row) {
            $csvData = [];
            /* @var $cell Cell */
            foreach ($this->getTable()->getCells() as $cell) {
                if ($this->isExcluded($cell)) continue;
                $cell->setRow($row);
                $csvData[$cell->getName()] = $cell->getValue();
                $cell->clearRow();
            }
            fputcsv(
                stream: $out,
                fields: $csvData,
                escape: ''
            );
        }

        fclose($out);
        exit;
    }

    /**
     * @callable function (\Tk\Table\Action\Csv $action, $obj): ?bool { }
     * @deprecated use addOnExecute()
     */
    public function addOnCsv(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnExecute()->append($callable, $priority);
        return $this;
    }

    /**
     * @return list<string>
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    /**
     * An array of cell names to exclude from the CSV data
     *
     * @param list<string> $excluded
     */
    public function setExcluded(array $excluded): static
    {
        $this->excluded = $excluded;
        return $this;
    }

    /**
     * Add to the cell exclude list
     * @param string|list<string> $excluded
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

    public function getRows(): array
    {
        return $this->rows;
    }

    public function setRows(array $rows): void
    {
        $this->rows = $rows;
    }

}