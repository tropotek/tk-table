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
     * @param null $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
    }


    /**
     *
     */
    public function execute()
    {

    }


    /**
     * @param mixed $obj
     * @return string|\Dom\Template
     */
    public function getCellHtml($obj)
    {
        $template = $this->__makeTemplate();
        $value = $this->getPropertyValue($obj, $this->getProperty());
        //vd($value);



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
    <a href="javascript:;" title="Move Order Up" rel="nofollow" class="btn btn-default btn-xs up noBlock" var="upUrl"><i class="fa fa-caret-up" var="upIcon"></i></a>
    <a href="javascript:;" title="Move Order Down" rel="nofollow" class="btn btn-default btn-xs dn noBlock" var="dnUrl"><i class="fa fa-caret-down" var="dnIcon"></i></a>
  </div>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}