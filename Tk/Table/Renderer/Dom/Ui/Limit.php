<?php
namespace Tk\Table\Renderer\Dom\Ui;

/**
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Limit extends Iface
{

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var array
     */
    private $limitList = null;




    /**
     * Create
     *
     * @param int $limit
     * @param array $limitList
     */
    public function __construct($limit = 0, $limitList = null)
    {
        $this->limit = $limit;
        if (!$limitList) {
            $limitList = array(10, 25, 50, 100, 250);
        }
        $this->limitList = $limitList;
    }

    /**
     * show
     *
     */
    public function show()
    {
        $template = $this->getTemplate();
        $domform = $template->getForm();



        $select = $domform->getFormElement('limit');
        foreach($this->limitList as $val) {
            $select->appendOption($val, $val);
        }

        $select->setValue($this->limit);
        $select->setAttribute('name', $this->makeInstanceKey(self::PARAM_LIMIT));

        $js = <<<JS
jQuery(function($) {

    function setUrlParam(url, name, value)
    {
        if (url.indexOf(name + "=") >= 0) {
            var prefix = url.substring(0, url.indexOf(name));
            var suffix = url.substring(url.indexOf(name));
            suffix = suffix.substring(suffix.indexOf("=") + 1);
            suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
            url = prefix + name + "=" + value + suffix;
        } else {
            if (url.indexOf("?") < 0) {
                url += "?" + name + "=" + value;
            } else {
                url += "&" + name + "=" + value;
            }
        }
        return url;
    }

    // Limit onchange event
    $('.tk-limit select').change(function(e) {
        if ($(this).val() == 0) {
            if (!confirm('WARNING: If there are many records this action could be slow.')) {
                return false;
            }
        }
        window.location.href = setUrlParam(window.location.href, $(this).attr('name'), $(this).val());
    });

});
JS;
        $template->appendJs($js);


        if (count($this->cssList)) {
            $template->addClass('tk-limit', $this->cssList);
        }
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="tk-limit" var="tk-limit">
    <select class="no2 form-control input-sm" name="limit" var="select">
      <option value="0">-- ALL --</option>
    </select>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}