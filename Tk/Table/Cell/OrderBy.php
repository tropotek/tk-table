<?php
namespace Tk\Table\Cell;


/**
 * Class OrderBy
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class OrderBy extends Text
{


    /**
     * The object class name we are ordering
     * @var string
     */
    protected $className = '';



    /**
     * OrderBy constructor.
     *
     * @param string $property
     * @param null|string $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
    }

    /**
     * @param \Tk\Table $table
     * @return Iface
     */
    public function setTable($table)
    {
        // Set table fixed order by (otherwise changing the sort order does not make any sense)
        $table->setFixedOrderBy('orderBy');
        return parent::setTable($table);
    }


    /**
     *
     */
    public function execute()
    {
        $list = $this->getTable()->getList();
        $obj = current($list);
        if ($list instanceof \Tk\Db\Map\ArrayObject) {
            $obj = $list->current();
        }
        if ($obj) {
            $this->className = get_class($obj);
        }
    }

    /**
     * May be a good method for the table itself????
     *
     * @param $idx
     * @return mixed|null
     */
    public function getListItem($idx)
    {
        $list = $this->getTable()->getList();
        if ($list instanceof \Tk\Db\Map\ArrayObject) {
            return $list->get($idx);
        }
        if (isset($list[$idx])) return $list[$idx];
        return null;
    }

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $template = $this->__makeTemplate();
        $value = $this->getPropertyValue($obj, $this->getProperty());
        //vd($value);

        $upObj = $this->getListItem($rowIdx-1);
        $dnObj = $this->getListItem($rowIdx+1);
        if ($upObj) {
            vd('upObj', $upObj->id);
            $upUrl = \Tk\Uri::create()->set($this->getTable()->makeInstanceKey('doOrderId'), $obj->id.'-'.$upObj->id);
            $template->setAttr('upUrl', 'href', $upUrl);
        }
        if ($dnObj) {
            vd('dnObj', $dnObj->id);
        }


        if ($rowIdx == 0) {
            $template->addClass('upUrl', 'disabled');
            $template->setAttr('upUrl', 'href', '#');
        }
        if ($this->getTable()->getList()->count() == $rowIdx+1) {
            $template->addClass('dnUrl', 'disabled');
            $template->setAttr('dnUrl', 'href', '#');
        }
        return $template;
    }



    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="text-center">
  <div class="btn-group" role="group">
    <a href="javascript:;" title="Move Order Up" rel="nofollow" class="btn btn-default btn-xs" var="upUrl"><i class="fa fa-caret-up" var="upIcon"></i></a>
    <a href="javascript:;" title="Move Order Down" rel="nofollow" class="btn btn-default btn-xs" var="dnUrl"><i class="fa fa-caret-down" var="dnIcon"></i></a>
  </div>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}