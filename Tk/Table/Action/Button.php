<?php
namespace Tk\Table\Action;

use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tk\Table\Action;

/**
 * A Button table action element
 */
class Button extends Action
{
    use AttributesTrait;

    protected string $icon = '';

    public function __construct(string $name, string $icon = '')
    {
        parent::__construct($name);
        $this->icon = $icon;
        $this->addCss('btn btn-sm btn-light');
        $this->setAttr('type', 'submit');
    }

    public static function create(string $name, string $icon = ''): self
    {
        $obj = new self($name, $icon);
        return $obj;
    }

    public function execute(): void
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(isset($_POST[$selectName]));
        if (!$this->isActive()) return;

        $value = trim($_POST[$selectName]);
        $this->getOnExecute()->execute($this, $value);

        Uri::create()->redirect();
    }

    /**
     * @note see `tkTable.js` for supporting JS to this action
     */
    public function getHtml(): string
    {
        $fieldName = $this->getTable()->makeRequestKey($this->getName());

        $icon = '';
        if ($this->icon) {
            $icon = sprintf('<i class="%s"></i> ', $this->icon);
        }

        return <<<HTML
<button name="{$fieldName}" value="{$fieldName}" class="{$this->getCssString()}" {$this->getAttrString()}>
    {$icon}{$this->getLabel()}
</button>
HTML;

    }

}