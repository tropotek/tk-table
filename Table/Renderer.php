<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table;

/**
 * The dynamic form renderer
 *
 *
 * @package Table
 */
class Renderer extends \Mod\Module
{

    /**
     * @var Table
     */
    protected $table = null;


    /**
     * @var \Form\StaticRenderer
     */
    protected $formRenderer = null;



    /**
     * Create the object instance
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->setTable($table);
        $this->setInstanceId($table->getInstanceId());
        $this->formRenderer = new \Form\Renderer($table->getForm());
        $this->formRenderer->showEnabled(false);
        $this->formRenderer->setInstanceId($this->getInstanceId());
    }

    /**
     * Create a new form with a new form renderer
     *
     * @param Table $table
     * @param ArrayIterator
     * @return Renderer
     */
    static function create($table)
    {
        $obj = new self($table);
        return $obj;
    }


    /**
     * init
     *
     */
    public function init()
    {
        tklog('Table_Renderer::init("'.$this->table->getId().'")');
        $this->addChild($this->formRenderer, $this->formRenderer->getForm()->getId());
        $this->table->init();
    }


    /**
     * execute
     *
     */
    public function doDefault()
    {
        tklog('Table_Renderer::doDefault("'.$this->table->getId().'")');
        $this->table->execute();
    }



    /**
     * Show
     *
     */
    public function show()
    {
        tklog('Table_Renderer::show("'.$this->table->getId().'")');
        $template = $this->getTemplate();

        $js = <<<JS
/**
 * Submit a form with an event attached so php scripts can fire the event.
 *
 * @param formElement form
 * @param string action
 * @param string value (optional) If not supplied, action is used.
 */
function tkFormSubmit(form, action) {
    var value = arguments[2] ? arguments[2] : action;
    if (!form) {
        return;
    }
    // Add the action event to a hidden field and value
    var node = document.createElement('input');
    node.setAttribute('type', 'hidden');
    node.setAttribute('name', action);
    node.setAttribute('value', value);
    form.appendChild(node);
    form.submit();
}
JS;
        $template->appendJs($js);
        // Table Events
        if (count($this->table->getActionList()) > 0 || count($this->table->getFilterList()) > 0) {
            $template->setChoice('TableEvents');
        }
        $this->showActions($this->table->getActionList());
        $this->showFilters($this->table->getFilterList());


        $this->showTh($this->table->getCellList());
        $list = $this->table->getList();

        if ($list && count($list) && $list instanceof \Tk\Db\ArrayObject) {
            $tool = $list->getTool();
            if ($tool) {
                $results = Ui\Results::createFromTool($tool);
                $results->show();
                $template->replaceTemplate('Results', $results->getTemplate());

                $pager = Ui\Pager::createFromTool($tool);
                $pager->show();
                $template->replaceTemplate('Pager', $pager->getTemplate());

                $limit = Ui\Limit::createFromTool($tool);
                $limit->show();
                $template->replaceTemplate('Limit', $limit->getTemplate());
            }
        }
        $this->showTd($list);

    }

    /**
     * Get the item object list array
     *
     * @return ArrayIterator
     */
    public function getList()
    {
        return $this->table->getList();
    }

    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     * @return Renderer
     */
    public function setTable(Table $table)
    {
        $this->setInstanceId($table->getInstanceId());
        $this->table = $table;
        return $this;
    }

    /**
     * Get the table object
     *
     * @return Table
     */
    public function getTable()
    {
    	return $this->table;
    }



    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $formId = $this->table->getForm()->getId();
        $tableId = $this->table->getTableId();
        $action = htmlentities($this->getUri()->toString());

        $xmlStr = <<<XML
<?xml version="1.0"?>
<div class="tk-table" id="$tableId">
  <form id="$formId" method="post" action="$action" class="form-inline">

    <!-- Table Header -->
    <div class="tableEvents" choice="TableEvents">
      <div class="filters" choice="filters">
        <div class="fieldBox" var="fields"></div>
        <div class="form-group events" var="events"><br/></div>
        <div class="clearfix"></div>
      </div>
      <div class="actions" choice="actions" var="action"></div>
    </div>

    <!-- Table -->
    <div class="tblWrap">
        <table border="0" cellpadding="0" cellspacing="0" class="table table-hover table-striped table-bordered table-condensed" var="tableData">
          <thead>
            <tr>
              <th var="th" repeat="th"></th>
            </tr>
          </thead>
          <tbody>
            <tr var="tr" repeat="tr">
              <td var="td" repeat="td">&#160;</td>
            </tr>
          </tbody>
        </table>
    </div>


    <!-- Table Controls -->
    <div class="row ctrlBox">
      <div class="col-md-3">
        <div var="Results"></div>
      </div>
      <div class="col-md-6">
        <div var="Pager"></div>
      </div>
      <div class="col-md-3">
        <div var="Limit"></div>
      </div>
    </div>

  </form>
</div>
XML;

        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

    /**
     * Render the filter fields
     *
     * @param array $filterList
     */
    public function showFilters($filterList)
    {
        if (!count($filterList)) {
            return;
        }
        $template = $this->getTemplate();
        $this->formRenderer->showEvents($template);
        $this->formRenderer->showFields($template);
        $template->setChoice('filters');
    }

    /**
     * Render the action icons
     *
     * @param array $actionList
     */
    public function showActions($actionList)
    {
        if (!count($actionList)) {
            return;
        }
        $template = $this->getTemplate();
        $template->setChoice('actions');
        /* @var $action Action\Iface */
        foreach ($actionList as $action) {
            $data = $action->getHtml($this->table->getList());
            if ($data instanceof \Dom\Template) {
                $template->appendTemplate('action', $data);
            } else {
                $template->appendHTML('action', $data);
            }
        }
    }


    /**
     * Render the table headers
     *
     * @param array $cellList
     */
    public function showTh($cellList)
    {
        $template = $this->getTemplate();
        /* @var $cell Cell\Iface */
        foreach ($cellList as $cell) {
            $repeat = $template->getRepeat('th');

            if ($cell->getOrderProperty()) {
                if ($cell->getOrder() == Cell\Iface::ORDER_ASC) {
                    $repeat->addClass('th', 'orderAsc');
                } else if ($cell->getOrder() == Cell\Iface::ORDER_DESC) {
                    $repeat->addClass('th', 'orderDesc');
                }
            }
            if ($cell->isKey()) {
                $repeat->addClass('th', 'key');
            }

            $data = $cell->getTh();
            if ($data === null) {
                $data = '&#160;';
            }
            if ($data instanceof \Dom\Template) {
                $repeat->insertTemplate('th', $data);
            } else {
                $repeat->insertHTML('th', $data);
            }
            $repeat->appendRepeat();
        }
    }

    /**
     * Render the table data rows
     *
     * @param array $list
     */
    public function showTd($list)
    {
        $template = $this->getTemplate();
        $idx = 0;
        /* @var $obj \Tk\Object */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            $rowClassArr = $this->insertRow($obj, $repeatRow);

            $rowClass = 'r_' . $idx . ' ' . $repeatRow->getAttr('tr', 'class') . ' ';
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ', $rowClassArr);
            }
            $rowClass = trim($rowClass);
            $repeatRow->addClass('tr', $rowClass);
            $repeatRow->appendRepeat();
            $idx++;
        }
    }

    /**
     * Insert an object's cells into a row
     *
     * @param \Tk\Object $obj
     * @param \Dom\Template $template The row repeat template
     * @return array
     */
    protected function insertRow($obj, $template)
    {
        $rowClassArr = array();
        /* @var $cell Cell\Interface */
        foreach ($this->table->getCellList() as $i => $cell) {
            if ($i == 0) {
                $cell->clearRowClass();
            }
            //$cell->clearCellClass();
            $repeat = $template->getRepeat('td');
            $data = $cell->getTd($obj);
            if ($data === null) {
                $data = '&#160;';
            }

            $repeat->addClass('td', 'm' . ucfirst($cell->getProperty()));
            if (count($cell->getCellClassList())) {
                $repeat->addClass('td', implode(' ', $cell->getCellClassList()));
            }
            if ($cell->isKey()) {
                $repeat->addClass('td', 'key');
            }
            $repeat->setAttr('td', 'title', $cell->getLabel());
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());

            if ($data instanceof \Dom\Template) {
                $repeat->insertTemplate('td', $data);
            } else {
                $repeat->insertHTML('td', $data);
            }
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }

}