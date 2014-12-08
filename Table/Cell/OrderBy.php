<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Cell;

/**
 * The dynamic table Cell
 *
 *
 * @package Table\Cell
 */
class OrderBy extends Iface
{


    /**
     * execute
     */
    public function execute($list)
    {
        if ($this->getRequest()->exists($this->getObjectKey('doOrderId'))) {
            $arr = explode('-', $this->getRequest()->get($this->getObjectKey('doOrderId')));
            $from = (int)$arr[0];
            $to = (int)$arr[1];
            if ($from < 0 || $to < 0 || $from > $list->count() || $to > $list->count()) {
                $this->ajaxMsg('Invalid parameters', 'error');
                return;
            }
            if ($from == $to) {
                $this->ajaxMsg('success');
                return;
            }
            $fromObj = $list->get($from);
            $toObj = $list->get($to);
            if (!$fromObj || !$toObj) {
                $this->ajaxMsg('Null object found', 'error');
                return;
            }
            try {
                \Tk\Db\Mapper::get($fromObj->getClassName())->orderSwap($fromObj, $toObj);
            } catch (\Exception $e) {
                $this->ajaxMsg($e->getMessage(), 'error');
            }
            $this->ajaxMsg('success');
        }


    }

    /**
     * ajaxMsg
     *
     * @param $msg
     * @param $status
     */
    public function ajaxMsg($msg, $status = 'ok')
    {
        if ($this->getRequest()->exists('_ajx')) {
            $obj = new \stdClass();
            $obj->msg = $msg;
            echo json_encode($obj);
            exit;
        } else {
            $url = $this->getUri();
            $url->delete($this->getObjectKey('doOrderId'));
            $url->redirect();
        }
    }


    /**
     * Get the table data from an object if available
     *
     * @param Tk\Db\Object $placement
     * @return string
     */
    public function getTd($placement)
    {
        static $i = 0;

        $upId = $i-1;
        if ($upId <= 0) {
            $upId = 0;
        }
        $dnId = $i+1;

        $urlUp = $this->getUri();
        $urlUp->set($this->getObjectKey('doOrderId'), $i.'-'.$upId);

        $urlDn = $this->getUri();
        $urlDn->set($this->getObjectKey('doOrderId'), $i.'-'.$dnId);

        $i++;
        $html = sprintf('<span style="text-align: center;display: inline-block; width: 100%%;"><a href="%s" title="Move Order Up" rel="nofollow" class="up noBlock"><i class="fa fa-caret-up"></i></a> &#160; &#160; <a href="%s" title="Move Order Down" rel="nofollow" class="dn noBlock"><i class="fa fa-caret-down"></i></a></span>',
        htmlentities($urlUp), htmlentities($urlDn));
        
        $template = \Mod\Dom\Loader::load($html);

        $template->appendJsUrl(\Tk\Url::create('/assets/tk-jslib/util.js'));
        $template->appendJsUrl(\Tk\Url::create('/assets/tk-jslib/Url.js'));

        $tid = $this->getTable()->getTableId();
        $id = $this->getTable()->getInstanceId();

        // NOTE: clean up javascript to enable multiple tables....?
        $js = <<<JS
jQuery(function($) {

    var origPos = 0;
    var newPos = 0;

    $('#$tid table tbody').sortable({
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        stop: function (e, ui) {
          newPos = ui.item.prevAll().length;
          reorder();
          var url = new Url(ui.item.find('.up').attr('href'));
          url.setField('doOrderId_' + $id, origPos + '-' + newPos);
          $.get(url.toString(), {_ajx: true}, function (data) {

          });
        },
        start: function (e, ui) {
          origPos = ui.item.prevAll().length;
        }
    }).disableSelection();

});

function reorder()
{
    $('#$tid table tbody tr').each(function(i, item) {
        var _class = trim($(this).attr('class').replace(/(odd|even|(r_[0-9]+)) ?/g, ''));
        if (i%2) {
            _class += ' odd';
        } else {
            _class += ' even';
        }
        _class += ' r_' + i;
        $(this).attr('class', trim(_class));

        var upId = i-1;
        if (upId <= 0) {
            upId = 0;
        }
        var dnId = i+1;
        if (dnId >= $('#$tid table tbody tr').length-1) {
            dnId = $('#$tid table tbody tr').length-1;
        }

        var upUrl = new Url($(this).find('.up').attr('href'));
        upUrl.setField('doOrderId_' + $id, i+'-'+upId);
        $(this).find('.up').attr('href', upUrl.toString())

        var dnUrl = new Url($(this).find('.dn').attr('href'));
        dnUrl.setField('doOrderId_' + $id, i+'-'+dnId);
        $(this).find('.dn').attr('href', dnUrl.toString());
    });

}
JS;
        $template->appendJs($js);

        return $template;
    }






}
