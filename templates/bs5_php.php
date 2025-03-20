<?php

use Tk\Table;
use Tk\Table\PhpRenderer;
use Tk\Table\TableRenderer;
use Tk\Uri;

/** @var \Tk\Table\Cell $cell */

/** @var array $rows */
$rows = $this->rows;

/** @var Table $table */
$table = $this->table;

/** @var PhpRenderer $renderer */
$renderer = $this;

// save default row attrs state
$rowAttrs = clone $table->getRowAttrs();


// results/Limit
$total = max(count($rows), $table->getTotalRows());
$from = $table->getOffset() + 1;
$to = $table->getOffset() + $table->getLimit();
if ($to > $total || $to == 0) {
    $to = $total;
}

// pager
$limit = $table->getLimit();
$page = $table->getPage();
$numPages = ceil($total / $limit);
if ($numPages < 2) return;

$startPage = 1;
$endPage = $renderer->getMaxPages();
$center = floor($renderer->getMaxPages() / 2);

if ($page > $center) {
    $startPage = $page - $center;
    $endPage = $startPage + $renderer->getMaxPages();
}

if ($startPage > $numPages - $renderer->getMaxPages()) {
    $startPage = $numPages - $renderer->getMaxPages();
    $endPage = $numPages;
}

if ($startPage < 1) {
    $startPage = 1;
}
if ($endPage >= $numPages) {
    $endPage = $numPages;
}
$pageUrl = \Tk\Uri::create();
$pageKey = $this->getTable()->makeRequestKey(Table::PARAM_PAGE);
$pageUrl->remove($pageKey);


?>
<!-- TODO: Include this script in the master template -->
<!--<script src="/vendor/ttek/tk-table/templates/tkTable.js" data-priority="1"></script>-->

<div class="tk-table" id="<?= $table->getId() ?>">

    <form method="post" class="tk-table-form">

        <? if ($table->getActions()->count()): ?>
            <div class="tk-actions">
                <?  foreach ($table->getActions() as $action): ?>
                    <?= $action->getHtml(); ?>
                <? endforeach ?>
            </div>
        <? endif ?>

        <div class="tk-table-wrapper table-responsive">
            <table class="table table-bordered table-hover <?= $table->getCssString() ?>" <?= $table->getAttrString() ?>>
                <thead class="table-light">
                <tr>
                    <? foreach ($table->getCells() as $cell): ?>
                        <th <?= $cell->getHeaderAttrs()->getAttrString(true) ?>>
                            <? if ($cell->isSortable()): ?>
                                <?
                                    // set header orderBy URL and css class
                                    $orderUrl = $cell->getOrderByUrl();
                                    $orderCss = '';
                                    $order = $this->getTable()->getOrderBy();
                                    $dir = '';
                                    if ($order[0] == '-') {
                                        $order = substr($order, 1);
                                        $dir = '-';
                                    }
                                    if ($order == $cell->getOrderBy()) {
                                        $orderCss = ($dir == '-') ? 'desc' : 'asc';
                                    }
                                ?>
                                <a class="noblock <?= $orderCss ?>" href="<?= $orderUrl ?>"><?= $cell->getHeader() ?></a>
                            <? else: ?>
                                <?= $cell->getHeader() ?>
                            <? endif ?>
                        </th>
                    <? endforeach ?>
                </tr>
                </thead>
                <tbody>
                <? foreach ($rows as $row): ?>
                    <?
                    $td = [];
                    foreach ($table->getCells() as $cell) {
                        $cellAttrs = $cell->getAttrList();
                        $val = $cell->getValue($row);
                        $td[$cell->getName()] = sprintf('<td %s>%s</td>', $cell->getAttrString(true), $val);
                        $cell->setAttrList($cellAttrs);
                    }
                    ?>
                    <tr <?= $table->getRowAttrs()->getAttrString(true) ?>>
                        <? foreach ($table->getCells() as $cell): ?>
                            <?= $td[$cell->getName()] ?>
                        <? endforeach ?>
                    </tr>
                    <? $table->setRowAttrs(clone $rowAttrs); ?>
                <? endforeach ?>
                </tbody>
            </table>
        </div>

        <? if($renderer->isFooterEnabled() && $total): ?>
            <div class="tk-foot row">

                <div class="tk-results col-3">
                    <? if($total): ?>
                        <small>
                            <span><?= $from ?></span>-<span><?= $to ?></span> of <span><?= $total ?></span> rows
                        </small>
                    <? endif ?>
                </div>
                <div class="tk-pager paging_simple_numbers col-6">
                    <? if($numPages > 1 && $this->getTable()->getLimit() != 0 && $total > $this->getTable()->getLimit()): ?>
                        <div class="row justify-content-center">
                            <ul class="pagination pagination-sm pagination-rounded col-auto">
                                <?
                                    $backUrl  = '#';
                                    $startUrl = '#';
                                    $disabled = '';
                                    if ($page > 1) {
                                        $backUrl  = $pageUrl->set($pageKey, $page-1)->toString();
                                        $startUrl = $pageUrl->set($pageKey, 1)->toString();
                                    } else {
                                        $disabled = TableRenderer::CSS_DISABLED;
                                    }
                                ?>
                                <li class="paginate_button page-item <?= $disabled ?>"><a class="page-link" href="<?= $startUrl ?>" title="Start Page" rel="nofollow">&lt;&lt;</a></li>
                                <li class="paginate_button page-item <?= $disabled ?>"><a class="page-link" href="<?= $backUrl ?>" title="Previous Page">&lt;</a></li>

                                <? for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?
                                        $selected = '';
                                        if ($i == $page) $selected = TableRenderer::CSS_SELECTED;
                                        $pageUrl->set($pageKey, $i);
                                        $url = $pageUrl->toString();
                                    ?>
                                    <li class="paginate_button page-item <?= $selected ?>"><a class="page-link" href="<?= $url ?>" title="Page <?= $i ?>" rel="nofollow"><?= $i ?></a></li>
                                <? endfor ?>

                                <?
                                    $nextUrl  = '#';
                                    $endUrl = '#';
                                    $disabled = '';
                                    if ($page < $endPage) {
                                        $nextUrl  = $pageUrl->set($pageKey, $page+1)->toString();
                                        $endUrl = $pageUrl->set($pageKey, $numPages)->toString();
                                    } else {
                                        $disabled = TableRenderer::CSS_DISABLED;
                                    }
                                ?>
                                <li class="paginate_button page-item <?= $disabled ?>"><a class="page-link" href="<?= $nextUrl ?>" title="Next Page">&gt;</a></li>
                                <li class="paginate_button page-item <?= $disabled ?>"><a class="page-link" href="<?= $endUrl ?>" title="Last Page" rel="nofollow">&gt;&gt;</a></li>
                            </ul>
                        </div>
                    <? endif ?>
                </div>

                <div class="tk-limit col-3">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <div class="btn-group dropup mb-2 me-1">
                                <? $limitLabel = $this->getTable()->getLimit() == 0 ? 'All' : strval($this->getTable()->getLimit()); ?>
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Results per page"><span><?= $limitLabel ?></span> <i class="mdi mdi-chevron-up"></i></button>
                                <div class="dropdown-menu">
                                    <a class="limit-link dropdown-item" href="#" repeat="limit-option">10</a>
                                    <? foreach (TableRenderer::LIMIT_LIST as $k => $v): ?>
                                        <? $url = Uri::create()->set($this->getTable()->makeRequestKey(Table::PARAM_LIMIT), strval($k)); ?>
                                        <a class="limit-link dropdown-item" href="<?= $url->toString() ?>"><?= strval($k) ?></a>
                                    <? endforeach ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <? endif ?>

    </form>

</div>