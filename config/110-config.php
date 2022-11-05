<?php
/**
 * Setup system configuration parameters
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
use Tk\Config;

return function (Config $config)
{
    // Register the TableBag session manager
    $tableBag = new \Tk\Table\TableBag();
    $config->getSession()->registerBag($tableBag);

};