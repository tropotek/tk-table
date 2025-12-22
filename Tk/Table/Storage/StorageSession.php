<?php

namespace Tk\Table\Storage;


use Tk\Collection;
use Tk\Session;

class StorageSession implements StorageInterface
{

    protected string $sessionId;
    protected ?Collection $session = null;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function get(): Collection
    {
        if (is_null($this->session)) {
            $this->session = Session::get($this->sessionId, new Collection());
            $this->save();
        }
        return $this->session;
    }

    public function save(): self
    {
        Session::set($this->sessionId, $this->session);
        return $this;
    }

    public function reset(): self
    {
        Session::remove($this->sessionId);
        $this->session = new Collection();
        return $this;
    }
}