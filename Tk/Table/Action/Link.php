<?php
namespace Tk\Table\Action;

use Dom\Template;
use Tk\Uri;

class Link extends ActionInterface
{

    protected string $icon = '';

    protected ?Uri $url = null;


    public function __construct(string $name, string|Uri $url = '', string $icon = '')
    {
        parent::__construct($name);
        if ($url) $this->setUrl($url);
        if ($icon) $this->setIcon($icon);
    }

    public function show(): ?Template
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('id', $btnId);

        $template = parent::show();

        if ($this->getIcon()) {
            $template->addCss('icon', $this->getIcon());
        } else {
            $template->setVisible('icon', false);
        }
        $template->appendHtml('text', $this->getLabel());

        if ($this->getUrl()) {
            $template->setAttr('btn', 'href', $this->getUrl());
        }

        // Add class names
        foreach($this->getCssList() as $v) {
            $template->addCss('btn', $v);
        }

        // Add new attribute values
        foreach($this->getAttrList() as $k => $v) {
            $template->setAttr('btn', $k, $v);
        }

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
