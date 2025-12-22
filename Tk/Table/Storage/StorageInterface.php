<?php

namespace Tk\Table\Storage;

use Tk\Collection;

interface StorageInterface
{

    public function get(): Collection;

    public function save(): self;

    public function reset(): self;

}