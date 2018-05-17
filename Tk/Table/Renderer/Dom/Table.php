<?php
namespace Tk\Table\Renderer\Dom;

use \Tk\Table\Cell;
use \Tk\Table\Renderer\Iface;
use Tk\Form;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Table extends Iface
{

    /**
     * @var \Dom\Repeat
     */
    protected $rowRepeat = null;

    /**
     * @var \Dom\Repeat
     */
    protected $cellRepeat = null;

    /**
     * @var array
     */
    protected $rowClassArr = array();

    /**
     * Enable the rendering of the pager individual page buttons
     * Set to false to only show next/prev buttons
     * @var bool
     */
    protected $pageButtons = true;


    /**
     * Create a new Renderer.
     *
     * @param \Tk\Table $table
     * @return Table
     */
    static function create($table = null)
    {
        $obj = new static($table);
        $table->addCss('table table-bordered table-striped table-hover');
        return $obj;
    }


    /**
     * Get the default form renderer object
     * 
     * @return Form\Renderer\Dom
     */
    public function getFormRenderer()
    {
        static $ren = null;
        if (!$ren)
            $ren = new \Tk\Form\Renderer\Dom($this->getTable()->getFilterForm());
        return $ren;
    }

    /**
     * @return bool
     */
    public function hasPageButtons()
    {
        return $this->pageButtons;
    }

    /**
     * @param bool $pageButtons
     */
    public function enablePageButtons($pageButtons)
    {
        $this->pageButtons = $pageButtons;
    }

    /**
     * Execute the renderer.
     * The returned object can be anything that you need to render
     * the output.
     *
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render Form
        if ($template->keyExists('var', 'filters')) {
            if (count($this->getTable()->getFilterForm()->getFieldList()) > 2) {
                $template->insertTemplate('filters', $this->getFormRenderer()->show());
                $template->setChoice('filters');
            }
        }

        $template->addCss('table', $this->getTable()->getCssString());
        $template->setAttr('table', $this->getTable()->getAttrList());

        $template->setAttr('form', 'id', $this->getTable()->getId().'_form');
        $template->setAttr('form', 'action', \Tk\Uri::create());
        $template->setAttr('form', 'method', 'post');
        $template->setAttr('fragment', 'name', $this->getTable()->getId());

        $this->showHeader();
        $this->showBody();

        $count = count($this->getTable()->getList());
        $countAll = count($this->getTable()->getList());
        $tool = $this->getTable()->getTool();
        if ($this->getTable()->getList() instanceof \Tk\Db\Map\ArrayObject) {
            $countAll = $this->getTable()->getList()->countAll();
            $tool = $this->getTable()->getList()->getTool();
        }

        //vd($count, $countAll, $tool->getLimit(), $countAll > $tool->getLimit());
        if ($this->hasFooter() && $count && $tool && $countAll > $tool->getLimit()) {

            // Results UI
            $results = Ui\Results::createFromDbTool($tool, $countAll);
            $results->setInstanceId($this->getTable()->getId());
            $results->addCss('col-xs-2');
            $this->appendFootRenderer($results);

            // Render Pager
            $pager = Ui\Pager::createFromDbTool($tool, $countAll);
            $pager->setEnablePageButtons($this->pageButtons);
            $pager->setInstanceId($this->getTable()->getId());
            $pager->addCss('col-xs-8 text-center');
            $this->appendFootRenderer($pager);

            // Limit UI
            $limitList = null;
            if ($this->getTable()->getParam('limitList')) {
                $limitList = $this->getTable()->getParam('limitList');
            }
            $limit = new Ui\Limit($tool->getLimit(), $limitList);
            $limit->setInstanceId($this->getTable()->getId());
            $limit->addCss('col-xs-2');
            $this->appendFootRenderer($limit);
        }

        $this->showFooter();

        return $template;
    }

    /**
     * Render the table header
     * Render header row and any filters, etc....
     *
     * @return mixed
     * @throws \Dom\Exception
     */
    protected function showHeader()
    {
        $template = $this->getTemplate();

        //  Show Actions
        if ($template->keyExists('var', 'actions')) {
            /* @var \Tk\Table\Action\Iface $action */
            foreach ($this->getTable()->getActionList() as $action) {
                if (!$action instanceof \Tk\Table\Action\Iface || !$action->isVisible()) continue;
                $html = $action->show();
                if ($html instanceof \Dom\Template) {
                    $template->appendTemplate('actions', $html);
                } else {
                    $template->appendHtml('actions', $html);
                }
                $template->setChoice('actions');
            }
        }

        if ($template->keyExists('repeat', 'th')) {
            /* @var \Tk\Table\Cell\Iface $cell */
            foreach ($this->getTable()->getCellList() as $property => $cell) {
                if (!$cell->isVisible()) continue;
                $repeat = $template->getRepeat('th');
                if (!$repeat) continue;
                if ($this->getTable()->getOrderProperty() == $cell->getOrderProperty()) {
                    if ($this->getTable()->getOrder() == \Tk\Table::ORDER_DESC) {
                        $repeat->addCss('th', 'orderDesc');
                    } else {
                        $repeat->addCss('th', 'orderAsc');
                    }
                }
                $data = $cell->getCellHeader();
                if ($data === null) {
                    $data = '&#160;';
                }
                if ($data instanceof \Dom\Template) {
                    $repeat->insertTemplate('th', $data);
                } else {
                    $repeat->insertHtml('th', $data);
                }

                $repeat->addCss('th', $cell->getCssString());
                $repeat->setAttr('th', 'data-label', $cell->getLabel());
                $repeat->setAttr('th', 'data-prop', $cell->getProperty());
                $repeat->appendRepeat();
            }
        }
    }

    /**
     * Render the table body
     *
     * @throws \Dom\Exception
     */
    protected function showBody()
    {
        $template = $this->getTemplate();
        $this->rowClassArr = array();
        $this->rowId = 0;
        if ($this->getTable()->getList() instanceof \Tk\Db\Map\ArrayObject) {
            $this->rowId = $this->getTable()->getList()->getTool()->getOffset();
        }

        if (!$template || !$template->keyExists('repeat', 'tr')) return;
        if (!$this->getTable()->getList()) return;
        foreach($this->getTable()->getList() as $i => $obj) {
            $this->rowRepeat = $template->getRepeat('tr');
            $this->showRow($obj);
            $this->rowRepeat->setAttr('tr', 'data-rowid', $this->rowId);
            $this->rowRepeat->appendRepeat();
            $this->rowId++;
        }
    }

    /**
     * Render the table row
     *
     * @param mixed $obj
     * @throws \Dom\Exception
     */
    protected function showRow($obj)
    {
        $rowCssList = array();
        $rowAttrList = array();

        if (!$this->rowRepeat || !$this->rowRepeat->keyExists('repeat', 'td')) return;

        /* @var \Tk\Table\Cell\Iface $cell */
        foreach($this->getTable()->getCellList() as $k => $cell) {
            if (!$cell->isVisible()) continue;
            $cell->storeProperties();
            $this->cellRepeat = $this->rowRepeat->getRepeat('td');
            $this->showCell($cell, $obj);
            $rowCssList = array_merge($rowCssList, $cell->getRow()->getCssList());
            $rowAttrList = array_merge($rowAttrList, $cell->getRow()->getAttrList());
            $this->cellRepeat->appendRepeat();
            $cell->resetProperties();
        }
        $this->rowRepeat->addCss('tr', trim(implode(' ', $rowCssList)) );
        foreach ($rowAttrList as $k => $v) {
            $this->rowRepeat->setAttr('tr', $k, $v);
        }
    }

    /**
     * Render the table cell
     *
     * @param Cell\Iface $cell
     * @param mixed $obj
     * @throws \Dom\Exception
     */
    protected function showCell(Cell\Iface $cell, $obj)
    {
        $html = $cell->getCellHtml($obj, $this->rowId);
        if (is_callable($cell->getOnCellHtml())) {
            $h = call_user_func_array($cell->getOnCellHtml(), array($cell, $obj, $html));
            if ($h !== null) $html = $h;
        }

        $this->cellRepeat->addCss('td', 'm' . ucfirst($cell->getProperty()));
        $this->cellRepeat->addCss('td', $cell->getCssString());
        foreach ($cell->getAttrList() as $name => $value) {
            $this->cellRepeat->setAttr('td', $name, $value);
        }
        if ($html === null) {
            $html = '&#160;';
        }
        if ($html instanceof \Dom\Template) {
            $this->cellRepeat->insertTemplate('td', $html);
        } else {
            $this->cellRepeat->insertHtml('td', $html);
        }
    }

    /**
     * Render the table footer
     * Render the table footer html like pagination etc...
     */
    protected function showFooter()
    {
        $template = $this->getTemplate();
        if (!$template->keyExists('var', 'foot')) return;

        // Render any footer widgets filters
        foreach($this->getFooterRenderList() as $renderer) {
            if ($renderer instanceof \Dom\Renderer\DisplayInterface) {      // TODO: should this reside here
                $renderer->show();
            }
            if ($renderer instanceof \Dom\Renderer\RendererInterface) {
                $template->appendTemplate('foot', $renderer->getTemplate());
            } else if ($renderer instanceof \Dom\Template) {
                $template->appendTemplate('foot', $renderer);
            } else {
                $this->cellRepeat->appendHtml('foot', $renderer);
            }
            $template->setChoice('foot');
        }
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-table" var="tk-table">
  <a name="" var="fragment"/>
  <div class="tk-filters" var="filters" choice="filters"></div>

  <form var="form">
      <div class="tk-actions" var="actions" choice="actions"></div>
      
      <div class="tk-table-wrap table-responsive">
        <table border="0" cellpadding="0" cellspacing="0" class="" var="table">
          <thead var="head">
            <tr>
              <th var="th" repeat="th"></th>
            </tr>
          </thead>
          <tbody class="table-body" var="body">
            <tr var="tr" repeat="tr">
              <td var="td" repeat="td">&#160;</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="tk-foot" choice="foot" var="foot"></div>
  </form>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}