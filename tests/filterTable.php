<?php

use Tk\Table\Cell;

require_once(__DIR__ . '/_prepend.php');

// create a table
// Tables have individual session stores for the current page,limit and sort values
// Use this or <CTRL + SHIFT +R> to reset the table session
$table = new Tk\Table('filter-table');
$table->setOrderBy('name');
$table->setLimit(10);

// create filter form
$filterForm = new Tk\Form('filter-form');
$filterForm->setMethod('get');
$filterForm->addCss('tk-table-filter');
// use the inline form template for tables
$tplFile = \Tk\Path::create('/vendor/ttek/tk-form/templates/bs5_dom_inline.html');
$filterRenderer = new \Tk\Form\Renderer\Dom\Renderer($filterForm, $tplFile);


$filterForm->appendField(new \Tk\Form\Field\Input('search'))
    ->setAttr('placeholder', 'Search: table name');

$list = ['' => '-- Size --', '1' => '< 1M', '2' => '1M - 2M', '3' => ' > 2Mb'];
$filterForm->appendField(new \Tk\Form\Field\Select('size', $list))->setValue('y');

$filterForm->appendField(new \Tk\Form\Action\Submit('reset', function (\Tk\Form $form, \Tk\Form\Action\ActionInterface $action) use($table) {
    $table->resetTableSession();
    \Tk\Uri::create()->reset()->redirect();
}))->addCss('btn-outline-secondary');


$filterDefaults = [
    'size' => 2
];
$filterForm->setFieldValues($filterDefaults);
$filterForm->execute($_GET);


// Add required table cells
$table->appendCell('actions')
    ->addHeaderCss('text-center')
    ->addCss('text-center')
    ->addOnHtml(function(\stdClass $obj, Cell $cell) {
        return <<<HTML
            <a class="btn btn-sm btn-outline-secondary" href="#" title="Edit"><i class="fa fa-fw fa-edit"></i></a>
        HTML;
    });

$table->appendCell('name')
    ->setSortable(true)
    ->addHeaderCss('max-width')
    ->addOnHtml(function(\stdClass $obj, Cell $cell) {
        return sprintf('<a href="#">%s</a>', $cell->getValue($obj));
    });

$table->appendCell('rows')
    ->setSortable(true)
    ->addHeaderCss('text-center')
    ->addCss('text-center');

$table->appendCell('size_b')
    ->setHeader('Size (bytes)')
    ->setSortable(true)
    ->addHeaderCss('text-center')
    ->addCss('text-center text-nowrap')
    ->addOnHtml(function(\stdClass $obj, Cell $cell) {
        return \Tk\FileUtil::bytes2String($obj->size_b);
    });


// execute table, runs execute callbacks on cells
$table->execute();

// get row data from a csv file
$file = __DIR__ . '/data/sis_dbsize.csv';
$rows = array_map('str_getcsv', file($file));
array_walk($rows, function(&$a) use ($rows) {
    $a = array_combine($rows[0], $a);
});
array_shift($rows);
$rows = array_map(fn($r) => (object)$r, $rows);

// Filter rows using filter form values
$filter = $filterForm->getFieldValues();
if (isset($filter['search'])) {
    $rows = array_filter($rows, function($row) use ($filter) {
        return stripos($row->name, $filter['search']) !== false;
    });
}
if (isset($filter['size'])) {
    $rows = match($filter['size']) {
        '1' => array_filter($rows, function($row) { return $row->size_b < 1000000; }),
        '2' => array_filter($rows, function($row) { return $row->size_b >= 1000000 && $row->size_b < 2000000; }),
        '3' => array_filter($rows, function($row) { return $row->size_b >= 2000000; }),
        default => $rows,
    };
}

// Sort Rows
$rows = \Tk\Table::sortRows($rows, $table->getOrderBy());

// Set paginated rows
$totalRows = count($rows);
$table->setRows($table->paginateRows($rows), $totalRows);

// Render the table
$renderer = new \Tk\Table\DomRenderer($table);
//$renderer = new \Tk\Table\PhpRenderer($table);
$tableHtml = $renderer->getHtml();

$formHtml = $filterRenderer->show()->toString();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Filter Table Example</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>
</head>
<body>

<?php include_once __DIR__ . '/inc/nav.php'; ?>


<div class="container my-5">
    <h1>Filter Table Example</h1>
    <div class="px-0">
        <p>
            This table renders an array of objects using a form to filter the results.
        </p>

        <?= $formHtml ?>
        <?= $tableHtml ?>

        <hr class="col-1 my-4">
        <p>Source code:</p>
        <?php highlight_file(__FILE__) ?>

    </div>
</div>
<script>
    jQuery(function ($) {

        $('input,select', '#filter-form').on('change', function () {
            // reset to page 1 on filter change
            $(this.form).append('<input type="hidden" name="filter-table_page" value="1">');
            // Submit the filter form
            this.form.submit();
        });

    });
</script>
</body>
</html>
