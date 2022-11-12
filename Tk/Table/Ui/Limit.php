<?php
namespace Tk\Table\Ui;

use Dom\Template;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Limit extends UiInterface
{

    protected int $limit = 0;

    private array $limitList = [];


    public function __construct(int $limit = 0, array $limitList = null)
    {
        $this->setLimit($limit);
        if (!$limitList) $limitList = [10, 25, 50, 100, 250];
        $this->setLimitList($limitList);
    }

    public static function create(int $limit = 0, array $limitList = null): static
    {
        return new self($limit, $limitList);
    }

    public function initFromResult(Result $list): static
    {
        return $this->initFromDbTool($list->getTool());
    }

    public function initFromDbTool(Tool $tool): static
    {
        $this->setLimit($tool->getLimit());
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

    public function getLimitList(): array
    {
        return $this->limitList;
    }

    public function setLimitList(array $limitList): static
    {
        $this->limitList = $limitList;
        return $this;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        if (!$this->isEnabled()) return $template;

        $domform = $template->getForm();
        vd($domform);
        if(!$domform) return $template;

        $template->addCss('limit', $this->getCssList());
        $template->setAttr('limit', $this->getAttrList());

        /** @var \Dom\Form\Select $select */
        $select = $domform->getFormElement('limit');
        foreach($this->limitList as $val) {
            $select->appendOption($val, $val);
        }

        $select->setValue($this->limit);
        $select->setAttribute('name', '');
        $select->setAttribute('data-name', $this->makeInstanceKey(self::PARAM_LIMIT));

        $js = <<<JS
jQuery(function($) {
    // Limit onchange event
    $('.tk-limit select').change(function(e) {
        if ($(this).val() === 0) {
            if (!confirm('WARNING: If there are many records this action could be slow.')) {
                return false;
            }
        }
        const searchParams = new URLSearchParams(location.search);
        searchParams.set($(this).data('name'), $(this).val());
        location.search = searchParams.toString();
        return false;
    });

});
JS;
        $template->appendJs($js);

        $template->setVisible('select');

        return $template;
    }

}