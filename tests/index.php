<?php
require_once(__DIR__ . '/_prepend.php');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TK Table Examples</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>
</head>
<body>

<?php include_once __DIR__ . '/inc/nav.php'; ?>


<div class="container my-5">
    <h1>Tk Table Examples</h1>
    <div class="col-lg-8 px-0">
        <p>
            The TK Table library is a PHP library for creating HTML tables.<br>
            Originally created to facilitate the creation of tables for
            <a href="https://github.com/tropotek/tk-framework" target="_blank">TkLib</a> PHP framework.
        </p>
        <p>
            Use these pages to test the library.
        </p>

        <hr class="col-1 my-4">

        <ul>
            <li><a href="arrayTable.php">Array Table</a></li>
            <li><a href="objectTable.php">Object Table</a></li>
            <li><a href="filterTable.php">Filtered Table</a></li>
        </ul>

        <hr class="col-1 my-4">

        <a href="https://tktable.tropotek.com/" class="btn btn-primary">Read the docs</a>
        <a href="https://github.com/tropotek/tk-table" class="btn btn-secondary">GitHub</a>
    </div>
</div>

</body>
</html>
