<?php
namespace Tk\Table\Ui;

use Dom\Template;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;


/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Results extends UiInterface
{

    private int $total = 0;

    private int $limit = 0;

    private int $offset = 0;


    public function __construct(int $total = 0, int $limit = 0, int $offset = 0)
    {
        $this->setTotal($total);
        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    public static function create(): static
    {
        return new self();
    }

    public function initFromResult(Result $list): static
    {
        $this->setTotal($list->countAll());
        return $this->initFromDbTool($list->getTool());
    }

    public function initFromDbTool(Tool $tool): static
    {
        if ($tool->getFoundRows())
            $this->setTotal($tool->getFoundRows());
        $this->setLimit($tool->getLimit());
        $this->setOffset($tool->getOffset());
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        if (!$this->isEnabled()) return $template;

        $template->addCss('results', $this->getCssString());
        foreach ($this->getAttrList() as $k => $v) {
            $template->setAttr('results', $k, $v);
        }

        if (!$this->total) {
            return $template;
        }

        $from = $this->offset+1;
        $to = $this->offset + $this->limit;
        if ($to > $this->total || $to == 0) {
            $to = $this->total;
        }
        $str = sprintf('%s-%s / %s', $from, $to, $this->total);
        $template->setAttr('results', 'title', $str);

        $template->setText('from', $from);
        $template->setText('to', $to);
        $template->setText('total', $this->total);

        $template->setVisible('values');
        return $template;
    }

}