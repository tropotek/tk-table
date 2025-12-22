<?php

namespace Tk\Table\Storage;


use Tk\Db\CollectionStatic;

class StorageDb implements StorageInterface
{

    protected string $sessionId;
    protected string $userId;
    protected CollectionStatic $session;

    public function __construct(string $sessionId, int $userId)
    {
        $this->sessionId = $sessionId;
        if (empty($sessionId)) {
            throw new \Exception('Session id is empty');
        }
        $this->userId = $userId;
        if (empty($userId)) {
            throw new \Exception('User id is empty');
        }

        $this->session = new CollectionStatic('user_data', $this->getSessionId());
        $this->save();
    }

    public function get(): CollectionStatic
    {
        return $this->session;
    }

    public function save(): self
    {
        $this->session->set($this->getSessionId(), $this->session);
        return $this;
    }

    public function reset(): self
    {
        $this->session->clear();
        return $this;
    }

    public function getSessionId(): string
    {
        return sprintf('tblstore-%s-%s', $this->sessionId, $this->userId);
    }
}