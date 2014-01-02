<?php

namespace Devster\WSSEBundle\Security\Authentication\Nonce;

class NonceRepositoryProvider
{
    protected $repositories = [];

    public function add($name, NonceRepositoryInterface $repository)
    {
        $this->repositories[$name] = $repository;
    }

    public function get($name)
    {
        if (! array_key_exists($name, $this->repositories)) {
            throw new \InvalidArgumentException(sprintf('NonceRepository with name `%s`, not found', $name));
        }

        return $this->repositories[$name];
    }
}
