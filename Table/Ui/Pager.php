<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Ui;

/**
 * A pager component that pagenates table data.
 *
 *
 * @package Table\Ui
 */
class Pager extends \Mod\Renderer
{
    const CSS_SELECTED = 'active';
    const CSS_DISABLED = 'disabled';

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
     * @var int
     */
    private $maxPages = 10;

    /**
     * @var Tk\Url
     */
    private $pageUrl = null;

    private $cssClass = '';



    /**
     * Create the pagenator class
     *
     * @param int $total The total number of records on all pages
     * @param int $limit
     * @param int $offset
     */
    public function __construct($total = 0, $limit = 25, $offset = 0)
    {
        $this->total = intval($total);
        $this->limit = intval($limit);
        $this->offset = intval($offset);
        $this->pageUrl = $this->getUri();
    }


    /**
     * Make a pager from a db tool object
     *
     * @param Tk\Db\Tool $tool
     * @return Table\Ui\Pager
     */
    static function createFromTool(\Tk\Db\Tool $tool)
    {
        $obj = new self($tool->getTotal(), $tool->getLimit(), $tool->getOffset());
        $obj->setInstanceId($tool->getInstanceId());
        return $obj;
    }

    /**
     * Make a results object from a db list
     *
     * @param \Tk\Db\ArrayObject $list
     * @return \Table\Ui\Pager
     */
//    static function createFromList($list)
//    {
//        if ($list->getDbTool()) {
//            return self::createFromTool($list->getDbTool());
//        }
//        return new self();
//    }

    /**
     * show
     *
     */
    public function show()
    {
        $currentPage = 0;
        $numPages = 1;
        $template = $this->getTemplate();

        if ($this->limit > -1 && $this->limit < $this->total) {
            $numPages = 0;
            $currentPage = 0;
            if ($this->limit > 0) {
                $numPages = ceil($this->total / $this->limit);
                $currentPage = ceil($this->offset / $this->limit);
            }

            $startPage = 0;
            $endPage = $this->maxPages;
            $center = floor($this->maxPages / 2);
            if ($currentPage > $center) {
                $startPage = $currentPage - $center;
                $endPage = $startPage + $this->maxPages;
            }
            if ($startPage > $numPages - $this->maxPages) {
                $startPage = $numPages - $this->maxPages;
                $endPage = $numPages;
            }

            if ($startPage < 0) {
                $startPage = 0;
            }
            if ($endPage >= $numPages) {
                $endPage = $numPages;
            }

            if ($numPages > 0) {
                $template->setChoice('Pager');
            }

            $pageUrl = $this->pageUrl;
            $pageUrl->delete($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET));
            for ($i = $startPage; $i < $endPage; $i++) {
                $repeat = $template->getRepeat('page');
                $repeat->insertText('pageUrl', $i + 1);
                $repeat->setAttr('pageUrl', 'title', 'Page ' . ($i + 1));
                $pageUrl->set($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET), $i * $this->limit);
                $repeat->setAttr('pageUrl', 'href', $pageUrl->toString());
                if ($i == $currentPage) {
                    $repeat->addClass('page', self::CSS_SELECTED);
                    $repeat->setAttr('pageUrl', 'title', 'Current Page ' . ($i + 1));
                }
                $repeat->appendRepeat();
            }

            if ($this->offset >= $this->limit) {
                $pageUrl->set($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET), $this->offset - $this->limit);
                $template->setAttr('backUrl', 'href', $pageUrl->toString());
                $template->setAttr('backUrl', 'title', 'Previous Page');
                $pageUrl->set($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET), 0);
                $template->setAttr('startUrl', 'href', $pageUrl->toString());
                $template->setAttr('startUrl', 'title', 'Start Page');
            } else {
                $template->addClass('start', self::CSS_DISABLED);
                $template->addClass('back', self::CSS_DISABLED);
            }

            if ($this->offset < ($this->total - $this->limit)) {
                $pageUrl->set($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET), $this->offset + $this->limit);
                $template->setAttr('nextUrl', 'href', $pageUrl->toString());
                $template->setAttr('nextUrl', 'title', 'Next Page');
                $pageUrl->set($this->getObjectKey(\Tk\Db\Tool::REQ_OFFSET), ($numPages - 1) * $this->limit);
                $template->setAttr('endUrl', 'href', $pageUrl->toString());
                $template->setAttr('endUrl', 'title', 'Last Page');
            } else {
                $template->addClass('end', self::CSS_DISABLED);
                $template->addClass('next', self::CSS_DISABLED);
            }
        }

        if ($this->cssClass) {
            $arr = explode(' ', $this->cssClass);
            foreach ($arr as $class) {
                $template->addClass('pager', $class);
            }
        }
        
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0"?>
<div class="tk-pager">
    <ul choice="Pager" var="pager" class="pagination pagination-sm">
      <li var="start"><a href="javascript:;" var="startUrl" rel="nofollow">Start</a></li>
      <li var="back"><a href="javascript:;" var="backUrl">&laquo;</a></li>
      <li repeat="page" var="page"><a href="javascript:;" var="pageUrl" rel="nofollow"></a></li>
      <li var="next"><a href="javascript:;" var="nextUrl">&raquo;</a></li>
      <li var="end"><a href="javascript:;" var="endUrl" rel="nofollow">End</a></li>
    </ul>
</div>
XML;
        return \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
    }

    /**
     * Set the maximum number of page values to display
     * Default: 10 page numbers
     *
     * @param int $i
     */
    public function setMaxPages($i)
    {
        $this->maxPages = $i;
        return $this;
    }

    /**
     * Set the new page Url, all pager urls will be createde from this url
     *
     * @param \Tk\Url $url
     */
    public function setPageUrl(\Tk\Url $url)
    {
        $this->pageUrl = $url;
        return $this;
    }

}