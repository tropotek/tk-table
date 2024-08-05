<?php
use Tk\Config;

return function (Config $config)
{
    // Register the TableBag session manager
    $tableBag = new \Tk\Table\TableBag();
    $config->set('session.bags.table', $tableBag);
};