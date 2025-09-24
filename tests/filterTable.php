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
            This table uses the Dom Template table renderer.
        </p>

        <hr class="col-1 my-4">
        <p>Source code:</p>
        <?php highlight_file(__FILE__) ?>

    </div>
</div>

</body>
</html>
