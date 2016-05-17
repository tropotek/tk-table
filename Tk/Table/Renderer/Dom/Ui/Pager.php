<?php
namespace Tk\Table\Renderer\Dom\Ui;

/**
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Pager extends Iface
{

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $maxPages = 10;

    /**
     * @var \Tk\Uri
     */
    protected $pageUrl = null;



    /**
     * Create
     *
     * @param int $total The total number of records on all pages
     * @param int $limit
     * @param int $offset
     */
    public function __construct($total = 0, $limit = 25, $offset = 0)
    {
        $this->total = (int)$total;
        $this->limit = (int)$limit;
        $this->offset = (int)$offset;

        $this->pageUrl = \Tk\Uri::create();
    }


    /**
     *
     * @param \Tk\Db\ArrayObject $list
     * @return Pager
     */
    static public function createFromDbArray(\Tk\Db\ArrayObject $list)
    {
        return new self($list->getFoundRows(), $list->getTool()->getLimit(), $list->getTool()->getOffset());
    }

    /**
     * Set the maximum number of page values to display
     * Default: 10 page numbers
     *
     * @param int $i
     * @return $this
     */
    public function setMaxPages($i)
    {
        $this->maxPages = $i;
        return $this;
    }

    /**
     * Set the new page Url, all pager urls will be created from this url
     *
     * @param \Tk\Uri $url
     * @return $this
     */
    public function setPageUrl(\Tk\Uri $url)
    {
        $this->pageUrl = $url;
        return $this;
    }


    /**
     * show
     *
     */
    public function show()
    {
        $template = $this->getTemplate();

        if (count($this->cssList)) {
            $template->addClass('tk-pager', $this->cssList);
        }

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
            $pageUrl->remove($this->makeInstanceKey(self::PARAM_OFFSET));
            for ($i = $startPage; $i < $endPage; $i++) {
                $repeat = $template->getRepeat('page');
                $repeat->insertText('pageUrl', $i + 1);
                $repeat->setAttr('pageUrl', 'title', 'Page ' . ($i + 1));
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), $i * $this->limit);
                $repeat->setAttr('pageUrl', 'href', $pageUrl->toString());
                if ($i == $currentPage) {
                    $repeat->addClass('page', self::CSS_SELECTED);
                    $repeat->setAttr('pageUrl', 'title', 'Current Page ' . ($i + 1));
                }
                $repeat->appendRepeat();
            }

            if ($this->offset >= $this->limit) {
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), $this->offset - $this->limit);
                $template->setAttr('backUrl', 'href', $pageUrl->toString());
                $template->setAttr('backUrl', 'title', 'Previous Page');
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), 0);
                $template->setAttr('startUrl', 'href', $pageUrl->toString());
                $template->setAttr('startUrl', 'title', 'Start Page');
            } else {
                $template->addClass('start', self::CSS_DISABLED);
                $template->addClass('back', self::CSS_DISABLED);
            }

            if ($this->offset < ($this->total - $this->limit)) {
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), $this->offset + $this->limit);
                $template->setAttr('nextUrl', 'href', $pageUrl->toString());
                $template->setAttr('nextUrl', 'title', 'Next Page');
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), ($numPages - 1) * $this->limit);
                $template->setAttr('endUrl', 'href', $pageUrl->toString());
                $template->setAttr('endUrl', 'title', 'Last Page');
            } else {
                $template->addClass('end', self::CSS_DISABLED);
                $template->addClass('next', self::CSS_DISABLED);
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
        $xhtml = <<<XHTML
<?xml version="1.0"?>
<nav class="tk-pager" var="tk-pager">
  <ul choice="Pager" var="pager" class="pagination pagination-sm">
    <li var="start"><a href="javascript:;" var="startUrl" rel="nofollow">Start</a></li>
    <li var="back"><a href="javascript:;" var="backUrl">&laquo;</a></li>

    <li repeat="page" var="page"><a href="javascript:;" var="pageUrl" rel="nofollow"></a></li>

    <li var="next"><a href="javascript:;" var="nextUrl">&raquo;</a></li>
    <li var="end"><a href="javascript:;" var="endUrl" rel="nofollow">End</a></li>
    </ul>
</nav>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}