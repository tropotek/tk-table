<?php
namespace Tk\Table\Renderer\Dom\Ui;

use Tk\Table;

/**
 * Class Iface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer
{
    use \Tk\Traits\InstanceKey;


    const PARAM_LIMIT = 'limit';
    const PARAM_OFFSET = 'offset';
    const PARAM_TOTAL = 'total';

    const CSS_SELECTED = 'active';
    const CSS_DISABLED = 'disabled';

    /**
     * @var array
     */
    protected $cssList = array();




    /**
     * Set the css classes to append to the root node
     *
     * @param $css
     */
    public function addCssClass($css)
    {
        $this->cssList[$css] = $css;
    }




}