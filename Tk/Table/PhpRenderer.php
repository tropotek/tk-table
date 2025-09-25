<?php
namespace Tk\Table;

use Tk\Table;

class PhpRenderer extends TableRenderer
{

    public function __construct(Table $table, string $templatePath = '')
    {
        if (!$templatePath) {
            $templatePath = dirname(__DIR__, 2) . '/templates/bs5_php.php';
        }
        parent::__construct($table, $templatePath);
    }

    public function getHtml(): string
    {
        ob_start();
        include($this->templatePath);
        $html = strval(ob_get_clean());
        return $html;
    }
}