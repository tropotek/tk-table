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

if ($numPages >= 2) {
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
}

// Render table rows first to capture and events triggered in the getValue() method
$tr = [];
foreach ($rows as $row) {

    $td = [];
    foreach ($table->getCells() as $cell) {
        $cellAttrs = $cell->getAttrList();
        //$val = $cell->getValue($row);
        $val = $cell->getHtml($row);
        $td[] = sprintf('<td %s>%s</td>', $cell->getAttrString(true), $val);
        $cell->setAttrList($cellAttrs);
    }
    $tr[] = sprintf('<tr %s>%s</tr>', $table->getRowAttrs()->getAttrString(true), implode("\n", $td));
    $table->setRowAttrs(clone $rowAttrs);
}
?>
<!-- TODO: Include this script in the master template -->
<!--<script src="/vendor/ttek/tk-table/templates/tkTable.js" data-priority="1"></script>-->

<div class="tk-table" id="<?= $table->getId() ?>">

    <form method="post" class="tk-table-form">

        <?php if ($table->getActions()->count()): ?>
            <div class="tk-actions">
                <?php  foreach ($table->getActions() as $action): ?>
                    <?= $action->getHtml(); ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <div class="tk-table-wrapper table-responsive">
            <table class="table table-hover <?= $table->getCssString() ?>" <?= $table->getAttrString() ?>>
                <thead class="table-light">
                <tr>
                    <?php foreach ($table->getCells() as $cell): ?>
                        <th <?= $cell->getHeaderAttrs()->getAttrString(true) ?>>
                            <?php if ($cell->isSortable()): ?>
                                <?php
                                    // Render table headers after table rows.
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
                            <?php else: ?>
                                <?= $cell->getHeader() ?>
                            <?php endif ?>
                        </th>
                    <?php endforeach ?>
                </tr>
                </thead>
                <tbody>
                    <?= implode("\n", $tr) ?>
                </tbody>
            </table>
        </div>

        <?php if($renderer->isFooterEnabled() && $total): ?>
            <div class="tk-foot row">

                <div class="tk-results col-md-3">
                    <?php if($total): ?>
                        <small>
                            <span><?= $from ?></span>-<span><?= $to ?></span> of <span><?= $total ?></span> rows
                        </small>
                    <?php endif ?>
                </div>
                <div class="tk-pager paging_simple_numbers col-md-6">
                    <?php if($numPages > 1 && $this->getTable()->getLimit() != 0 && $total > $this->getTable()->getLimit()): ?>
                        <div class="row justify-content-center">
                            <ul class="pagination pagination-sm pagination-rounded col-auto">
                                <?php
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
                                <li class="page-item <?= $disabled ?>"><a class="page-link" href="<?= $startUrl ?>" title="Start Page" rel="nofollow">&lt;&lt;</a></li>
                                <li class="page-item <?= $disabled ?>"><a class="page-link" href="<?= $backUrl ?>" title="Previous Page">&lt;</a></li>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?php
                                        $selected = '';
                                        if ($i == $page) $selected = TableRenderer::CSS_SELECTED;
                                        $pageUrl->set($pageKey, $i);
                                        $url = $pageUrl->toString();
                                    ?>
                                    <li class="page-item <?= $selected ?>"><a class="page-link" href="<?= $url ?>" title="Page <?= $i ?>" rel="nofollow"><?= $i ?></a></li>
                                <?php endfor ?>

                                <?php
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
                                <li class="page-item <?= $disabled ?>"><a class="page-link" href="<?= $nextUrl ?>" title="Next Page">&gt;</a></li>
                                <li class="page-item <?= $disabled ?>"><a class="page-link" href="<?= $endUrl ?>" title="Last Page" rel="nofollow">&gt;&gt;</a></li>
                            </ul>
                        </div>
                    <?php endif ?>
                </div>

                <div class="tk-limit col-md-3">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <div class="btn-group dropup mb-2 me-1">
                                <?php $limitLabel = $this->getTable()->getLimit() == 0 ? 'All' : strval($this->getTable()->getLimit()); ?>
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Results per page"><span><?= $limitLabel ?></span> <i class="mdi mdi-chevron-up"></i></button>
                                <div class="dropdown-menu">
                                    <a class="limit-link dropdown-item" href="#" repeat="limit-option">10</a>
                                    <?php foreach (TableRenderer::LIMIT_LIST as $k => $v): ?>
                                        <?php $url = Uri::create()->set($this->getTable()->makeRequestKey(Table::PARAM_LIMIT), strval($k)); ?>
                                        <a class="limit-link dropdown-item" href="<?= $url->toString() ?>"><?= strval($k) ?></a>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif ?>

    </form>

</div>