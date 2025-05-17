<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;

/**
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
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

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnDelete(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->addOnExecute($callable, $priority);
        return $this;
    }

}