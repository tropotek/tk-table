<?php
namespace Tk\Table\Renderer\Dom\Ui;

/**
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
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
     *
     * @param \Tk\Db\Map\ArrayObject $list
     * @return Pager
     */
    public static function createFromDbArray(\Tk\Db\Map\ArrayObject $list)
    {
        return new self($list->getFoundRows(), $list->getTool()->getLimit(), $list->getTool()->getOffset());
    }

    /**
     * show
     *
     */
    public function show()
    {
        $template = $this->getTemplate();

        if (count($this->cssList)) {
            $template->addCss('tk-results', $this->cssList);
        }

        if (!$this->total) {
            $template->insertText('tk-results', 'No Results Found.');
            return;
        }

        $off = 0;
        if ($this->total) {
            $off = $this->offset+1;
        }

        $template->insertText('from', $off);
        $to = $this->offset + $this->limit;
        if ($to > $this->total) {
            $to = $this->total;
        }
        $template->insertText('to', $to);
        $template->insertText('total', $this->total);

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
  Records: <span var="from"></span> to <span var="to"></span> of <span var="total"></span>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}