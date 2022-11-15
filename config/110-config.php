<?php
use Tk\Config;

return function (Config $config)
{
    // Register the TableBag session manager
    $tableBag = new \Tk\Table\TableBag();
    $config->getSession()->registerBag($tableBag);
};