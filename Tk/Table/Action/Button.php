<?php
namespace Tk\Table\Action;

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Uri;

class Button extends ActionInterface
{

    protected string $icon = '';

    protected ?Uri $url = null;


    public function __construct(string $name, string $icon = '')
    {
        parent::__construct($name);
        $this->setIcon($icon);
    }

    public function execute(Request $request)
    {
        parent::execute($request);

        if (!$this->isTriggered()) return;

        $this->getUrl()?->redirect();
    }

    public function show(): ?Template
    {
        $template = parent::show();

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('id', $btnId);
        $this->setAttr('name', $btnId);
        $this->setAttr('value', $btnId);

        if ($this->getIcon()) {
            $template->addCss('icon', $this->getIcon());
            $template->setVisible('icon');
        }
        $template->appendHtml('text', $this->getLabel());

        // Add class names
        $template->addCss('btn', $this->getCssList());
        $template->setAttr('btn', $this->getAttrList());

        return $template;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getUrl(): ?Uri
    {
        return $this->url;
    }

    public function setUrl(Uri|string $url): static
    {
        $this->url = Uri::create($url);
        return $this;
    }

}
