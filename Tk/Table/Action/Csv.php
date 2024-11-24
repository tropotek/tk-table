<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;
use Tk\Uri;
use Tk\Table\Action;
use Tk\Table\Cell;
use Tk\Table\Cell\OrderBy;
use Tk\Table\Cell\RowSelect;

/**
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Csv extends Select
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
        $this->removeAttr('disabled');
    }

    public static function create(string $name = 'export', string $icon = 'far fa-fw fa-list-alt'): self
    {
        $obj = new self($name);
        $obj->icon = $icon;
        $obj->setConfirmStr('Export selected records to CSV?');
        return $obj;
    }

    public function execute(): void
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(isset($_POST[$selectName]));
        if (!$this->isActive()) return;

        $selected = $this->getOnGetSelected()->execute();
        if (!is_array($selected)) {
            $selected = [];
        }

        $rows = $this->getOnExecute()->execute($this, $selected);
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
            throw new \Exception("failed to open output stream");
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