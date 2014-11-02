<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Ui;

/**
 * A component to render the results pager data...
 *
 *
 * @package Table\Ui
 */
class Results extends \Mod\Renderer
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
     * Make a pager from a db tool object
     *
     * @param \Tk\Db\Tool $tool
     * @return \Table\Ui\Results
     */
    static function createFromTool(\Tk\Db\Tool $tool)
    {
        $obj = new self($tool->getTotal(), $tool->getLimit(), $tool->getOffset());
        $obj->setInstanceId($tool->getInstanceId());
        return $obj;
    }

    /**
     * Render the widget.
     *
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->insertText('from', $this->offset + 1);
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
     * @return Dom_Template
     */
    public function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<div class="tk-results">
  Showing <span var="from"></span> to <span var="to"></span> of <span var="total"></span> entries.
</div>';
        return \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
    }
}