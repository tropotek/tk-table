<?php
namespace Tk\Table\Renderer\Dom\Ui;

/**
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Results extends Iface
{

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var int
     */
    private $offset = 0;


    /**
     * Create the object instance
     *
     * @param int $total
     * @param int $limit
     * @param int $offset
     */
    public function __construct($total = 0, $limit = 0, $offset = 0)
    {
        $this->total = intval($total);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
    }

    /**
     * @param \Tk\Db\Map\ArrayObject $list
     * @return Results
     */
    public static function createFromDbArray(\Tk\Db\Map\ArrayObject $list)
    {
        return new self($list->countAll(), $list->getTool()->getLimit(), $list->getTool()->getOffset());
    }

    /**
     * @param \Tk\Db\Tool $tool
     * @param int $foundRows
     * @return Results
     */
    public static function createFromDbTool($tool, $foundRows = 0)
    {
        return new self($foundRows, $tool->getLimit(), $tool->getOffset());
    }

    /**
     * show
     *
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->addCss('tk-results', $this->getCssString());
        foreach ($this->getAttrList() as $k => $v) {
            $template->setAttr('tk-results', $k, $v);
        }

        if (!$this->total) {
//            $template->insertText('tk-results', 'No Results Found.');
            return;
        }

        $from = 0;
        if ($this->total) {
            $from = $this->offset+1;
        }
        $to = $this->offset + $this->limit;

        if ($to > $this->total || $to == 0) {
            $to = $this->total;
        }

        $str = sprintf('%s-%s / %s', $from, $to, $this->total);
        //$template->insertText('tk-results', $str);
        $template->setAttr('tk-results', 'title', $str);

        // TODO could we just insert the string
        $template->insertText('from', $from);
        $template->insertText('to', $to);
        $template->insertText('total', $this->total);
        $template->show('tk-results');
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="tk-results" var="tk-results">
  <span choice="tk-results">
    <span var="from"></span>-<span var="to"></span> of <span var="total"></span>
  </span>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}