<?php

namespace Tk\Table;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Tk\Factory;

class TableBag extends AttributeBag
{

    /**
     * @param string $storageKey The key used to store attributes in the session
     */
    public function __construct(string $storageKey = '_ttek_tables')
    {
        parent::__construct($storageKey);
        $this->setName(self::class);
    }

    public static function getTableSession($tableId): TableSession
    {
        /** @var TableBag $bag */
        $bag = Factory::instance()->getSession()->getBag(TableBag::class);
        if (!$bag->has($tableId)) {
            $ses = new TableSession($tableId);
            $bag->set($tableId, $ses);
        }
        return $bag->get($tableId);
    }

    public static function removeTableSession($tableId): bool
    {
        /** @var TableBag $bag */
        $bag = Factory::instance()->getSession()->getBag(TableBag::class);
        if ($bag->has($tableId)) {
            $bag->remove($tableId);
            return true;
        }
        return false;
    }

}