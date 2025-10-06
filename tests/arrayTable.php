<?php

use Tk\Table\Cell;

require_once(__DIR__ . '/_prepend.php');


// create a table
// Tables have individual session stores for the current page,limit and sort values
// Use this or <CTRL + SHIFT +R> to reset the table session
$table = new Tk\Table('array-table');
$table->setOrderBy('name');
$table->setLimit(10);

// Add required cells
$table->appendCell('actions')
    ->addHeaderCss('text-center')
    ->addCss('text-center')
    ->addOnHtml(function(array $obj, Cell $cell) {
        return <<<HTML
            <a class="btn btn-outline-secondary" href="#" onclick="alert('Edit Row')" title="Edit"><i class="fa fa-fw fa-edit"></i></a>
        HTML;
    });

$table->appendCell('name')
    ->setSortable(true)
    ->addHeaderCss('max-width')
    ->addOnHtml(function(array $obj, Cell $cell) {
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
    ->addOnHtml(function(array $obj, Cell $cell) {
        return \Tk\FileUtil::bytes2String($obj['size_b']);
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
//$rows = array_map(fn($r) => (object)$r, $rows);
$totalRows = count($rows);

// Sort Rows
$rows = \Tk\Table::sortRows($rows, $table->getOrderBy());

// Set paginated rows
$table->setRows($table->paginateRows($rows), $totalRows);

// Render the table
$renderer = new \Tk\Table\PhpRenderer($table);
//$renderer = new \Tk\Table\DomRenderer($table);
$tableHtml = $renderer->getHtml();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Array Table Example</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>
</head>
<body>

<?php include_once __DIR__ . '/inc/nav.php'; ?>


<div class="container my-5">
    <h1>Array Table Example</h1>
    <div class=" px-0">
        <p>
            This table renders an array of arrays.
        </p>

        <?= $tableHtml ?>

        <hr class="col-1 my-4">
        <p>Source code:</p>
        <?php highlight_file(__FILE__) ?>

    </div>
</div>

</body>
</html>
