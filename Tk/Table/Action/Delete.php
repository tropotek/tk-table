<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;
use Tk\Db;
use Tk\Db\Model;
use Tk\Table\Cell\RowSelect;
use Tk\Table\Exception;

/**
 * Add a delete row action.
 * Example:
 * ```
 *     $this->appendAction(Delete::create()
 *         ->addOnExecute(function(Delete $action) use ($rowSelect) {
 *             $selected = $rowSelect->getSelected();
 *             foreach ($selected as $file_id) {
 *                 Db::delete('file', compact('file_id'));
 *             }
 *         })
 *     );
 * ```
 */
class Delete extends Select
{

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->icon = 'fa fa-fw fa-trash';
        $this->setAttr('title', 'Delete Selected Records');
        $this->setConfirmStr('Delete the selected records?');
    }

    public static function create(string $name = 'delete', string $icon = 'fa fa-fw fa-trash'): self
    {
        $obj = new self($name);
        $obj->icon = $icon;
        return $obj;
    }

    public static function createDefault(string $class, ?RowSelect $rowSelect = null): self
    {
        if (!in_array(Model::class, class_parents($class))) {
            throw new Exception("class must be a Db Model");
        }

        $obj = new self('delete');
        $obj->addOnExecute(function(Delete $action) use ($class, $rowSelect) {
            $selected = $rowSelect->getSelected();
            foreach ($selected as $id) {
                Db::delete($class::getDbTable(), [$class::getPrimaryColumn() => $id]);
            }
        });
        return $obj;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     * @deprecated use addOnExecute()
     */
    public function addOnDelete(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->addOnExecute($callable, $priority);
        return $this;
    }

}