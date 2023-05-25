<?php
namespace Tk\Table\Ui;

use Dom\Template;
use Tk\Db\Mapper\Result;
use Tk\Uri;

class Pager extends UiInterface
{

    protected int $total = 0;

    protected int $limit = 0;

    protected int $offset = 0;

    protected int $maxPages = 10;

    protected Uri $pageUrl;


    public function __construct(int $total = 0, int $limit = 25, int $offset = 0)
    {
        $this->setTotal($total);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setPageUrl(\Tk\Uri::create());
    }

    public static function create(int $total = 0, int $limit = 25, int $offset = 0): static
    {
        return new self();
    }

    public function initFromResult(Result $list): static
    {
        $this->setTotal($list->countAll());
        return $this->initFromDbTool($list->getTool());
    }

    public function initFromDbTool($tool): static
    {
        if ($tool->getFoundRows()) {
            $this->setTotal($tool->getFoundRows());
        }
        $this->setLimit($tool->getLimit());
        $this->setOffset($tool->getOffset());
        return $this;
    }

    /**
     * Set the maximum number of page values to display
     */
    public function setMaxPages(int $i): static
    {
        $this->maxPages = $i;
        return $this;
    }

    /**
     * Set the new page Url, all pager urls will be created from this url
     */
    public function setPageUrl(Uri $url): static
    {
        $this->pageUrl = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return Pager
     */
    public function setTotal(int $total): static
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        if (!$this->isEnabled()) return $template;

        $template->addCss('pager', $this->getCssString());
        foreach ($this->getAttrList() as $k => $v) {
            $template->setAttr('pager', $k, $v);
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
                $template->setVisible('values');
            }

            $pageUrl = $this->pageUrl;

            $pageUrl->remove($this->makeInstanceKey(self::PARAM_OFFSET));

            for ($i = $startPage; $i < $endPage; $i++) {
                $repeat = $template->getRepeat('page');
                $repeat->setText('pageUrl', $i + 1);
                $repeat->setAttr('pageUrl', 'title', 'Page ' . ($i + 1));
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), $i * $this->limit);
                $repeat->setAttr('pageUrl', 'href', $pageUrl->toString());
                if ($i == $currentPage) {
                    $repeat->addCss('page', self::CSS_SELECTED);
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
                $template->addCss('start', self::CSS_DISABLED);
                $template->addCss('back', self::CSS_DISABLED);
            }

            if ($this->offset < ($this->total - $this->limit)) {
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), $this->offset + $this->limit);
                $template->setAttr('nextUrl', 'href', $pageUrl->toString());
                $template->setAttr('nextUrl', 'title', 'Next Page');
                $pageUrl->set($this->makeInstanceKey(self::PARAM_OFFSET), ($numPages - 1) * $this->limit);
                $template->setAttr('endUrl', 'href', $pageUrl->toString());
                $template->setAttr('endUrl', 'title', 'Last Page');
            } else {
                $template->addCss('end', self::CSS_DISABLED);
                $template->addCss('next', self::CSS_DISABLED);
            }
        }

        return $template;
    }

}