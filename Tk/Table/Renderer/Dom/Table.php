<?php
namespace Tk\Table\Renderer\Dom;

use \Tk\Table\Cell;
use \Tk\Table\Renderer\Iface;
use Tk\Form;

/**
 * Class Table
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
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
     * Create a new Renderer.
     *
     * @param \Tk\Table $table
     * @return Table
     */
    static function create($table = null)
    {
        $obj = new static($table);

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
     * Execute the renderer.
     * The returned object can be anything that you need to render
     * the output.
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();
        //$this->getTable()->execute();

        // Render Form
        if ($template->keyExists('var', 'filters')) {
            if (count($this->getTable()->getFilterForm()->getFieldList()) > 2) {
                $fren = $this->getFormRenderer()->show();
                $template->insertTemplate('filters', $fren->getTemplate());
                $template->setChoice('filters');
            }
        }

        // render outer table wrapper (IE: <table> tag stuff)
        //$template->setAttr('table', 'id', $this->getTable()->getId());

        $template->addCss('table', $this->getTable()->getCssString());
        foreach($this->getTable()->getAttrList() as $k => $v) {
            $template->setAttr('table', strip_tags($k), $v);
        }


        $template->setAttr('form', 'id', $this->getTable()->getId().'_form');
        $template->setAttr('form', 'action', \Tk\Uri::create());
        $template->setAttr('form', 'method', 'post');

        $this->showHeader();

        $this->showBody();

        if ($this->isFooterEnabled() && count($this->getTable()->getList()) && $this->getTable()->getList() instanceof \Tk\Db\Map\ArrayObject && $this->getTable()->getList()->getTool()) {
            // Results UI
            $results = Ui\Results::createFromDbArray($this->getTable()->getList());
            $results->setInstanceId($this->getTable()->getId());
            $results->addCss('col-xs-2');
            $this->appendFootRenderer($results);

            // Render Pager
            $pager = Ui\Pager::createFromDbArray($this->getTable()->getList());
            $pager->setInstanceId($this->getTable()->getId());
            $pager->addCss('col-xs-8 text-center');
            $this->appendFootRenderer($pager);

            // Limit UI
            $limitList = null;
            if ($this->getTable()->getParam('limitList')) {
                $limitList = $this->getTable()->getParam('limitList');
            }
            $limit = new Ui\Limit($this->getTable()->getList()->getTool()->getLimit(), $limitList);
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
     */
    protected function showHeader()
    {
        $template = $this->getTemplate();

        //  Show Actions

        if ($template->keyExists('var', 'actions')) {
            /* @var \Tk\Table\Action\Iface $action */
            foreach ($this->getTable()->getActionList() as $action) {
                if (!$action instanceof \Tk\Table\Action\Iface || !$action->isVisible()) continue;
                $html = $action->getHtml();
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
     * @return mixed
     */
    protected function showBody()
    {
        $template = $this->getTemplate();
        $this->rowClassArr = array();
        $this->rowId = 0;
        if ($this->getTable()->getList() instanceof \Tk\Db\Map\ArrayObject) {
            $this->rowId = $this->getTable()->getList()->getTool()->getOffset();
        }
        
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
     * @return mixed
     */
    protected function showRow($obj)
    {
        $rowCssList = array();
        $rowAttrList = array();
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
     * @return mixed
     */
    protected function showCell(Cell\Iface $cell, $obj)
    {
        $html = $cell->getCellHtml($obj, $this->rowId);
        if (is_callable($cell->getOnCellHtml())) {
            $h = call_user_func_array($cell->getOnCellHtml(), array($cell, $obj, $html));
            if ($h) $html = $h;
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
     *
     * @return mixed
     */
    protected function showFooter()
    {
        $template = $this->getTemplate();
        if (!$template->keyExists('var', 'foot')) return;
        // Render any footer widgetsfilters
        foreach($this->getFooterRenderList() as $renderer) {
            // TODO: should this reside here
            if ($renderer instanceof \Dom\Renderer\DisplayInterface) {
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

  <div class="tk-filters" var="filters" choice="filters"></div>

  <form var="form">
      <div class="tk-actions" var="actions" choice="actions"></div>
      
      <div class="tk-table-wrap table-responsive">
        <table border="0" cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-hover" var="table">
          <thead var="head">
            <tr>
              <th var="th" repeat="th"></th>
            </tr>
          </thead>
          <tbody var="body">
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