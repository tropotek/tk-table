<?php
namespace Tk\Table\Renderer\Dom;

use \Tk\Table\Cell;
use Tk\Form;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class Div extends Table
{

    /**
     * @param \Tk\Table $table
     * @return Div
     */
    static function create($table = null)
    {
        $obj = new static($table);
        return $obj;
    }

    /**
     * Execute the renderer.
     * The returned object can be anything that you need to render
     * the output.
     *
     * @return mixed
     */
    public function show()
    {
        $template = parent::show();

        return $template;
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
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-table div-table" var="table">
  <a name="" var="fragment"/>
  <div class="tk-filters" var="filters" choice="filters"></div>

  <form var="form">
    <div class="tk-actions" var="actions" choice="actions"></div>
    
    <!-- Table -->
    <div class="tk-table-wrap">
      <div class="table table-body" var="table-body">
        <div class="tr" var="tr" repeat="tr">
          <div class="row-group"> 
            <div class="td" var="td" repeat="td"></div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="tk-foot" choice="foot" var="foot"></div>
  </form>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}