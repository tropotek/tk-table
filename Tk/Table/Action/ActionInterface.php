<?php
namespace Tk\Table\Action;

use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\CallbackCollection;
use Tk\Table;
use Tk\Ui\Element;

abstract class ActionInterface extends Element implements DisplayInterface
{
    use RendererTrait;

    /**
     * This will be used for the event name using the instance ID
     */
    protected string $name = '';

    protected string $label = '';

    protected Table $table;

    protected bool $visible = true;

    public CallbackCollection $onInit;

    public CallbackCollection $onExecute;

    public CallbackCollection $onShow;


    public function __construct(string $name)
    {
        $this->onInit    = CallbackCollection::create();
        $this->onExecute = CallbackCollection::create();
        $this->onShow    = CallbackCollection::create();

        $this->setName($name);
        $this->setLabel(ucfirst(preg_replace('/[A-Z]/', ' $0', $name)));
        $this->addCss('a'.ucFirst($name));
    }

    public function init()
    {
        $this->getOnInit()->execute($this);
    }

    public function execute(Request $request)
    {
        if (!$this->isTriggered()) return;
        $this->getOnExecute()->execute($this, $request);
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $this->getOnShow()->execute($this);
        return $template;
    }

    /**
     * Has this button action been fired
     */
    public function isTriggered(): bool
    {
        $request = $this->getRequest();
        return $request->request->has($this->getTable()->makeInstanceKey($this->getName()));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $name = preg_replace('/[^a-z0-9_-]/i', '_', $name);
        $this->name = $name;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $str): static
    {
        $this->label = $str;
        return $this;
    }

    public function setTable(Table $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Eg: function (ActionInterface $action) { }
     */
    public function addOnInit(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnInit()->append($callable, $priority);
        return $this;
    }

    public function getOnInit(): CallbackCollection
    {
        return $this->onInit;
    }

    /**
     * Eg: function (ActionInterface $action, Request $request) { }
     */
    public function addOnExecute(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnExecute()->append($callable, $priority);
        return $this;
    }

    public function getOnExecute(): CallbackCollection
    {
        return $this->onExecute;
    }

    /**
     * Eg: function (ActionInterface $action) { }
     */
    public function addOnShow(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnShow()->append($callable, $priority);
        return $this;
    }

    public function getOnShow(): CallbackCollection
    {
        return $this->onShow;
    }

}
