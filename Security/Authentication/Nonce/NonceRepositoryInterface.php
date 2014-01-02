<?php

namespace Devster\WSSEBundle\Security\Authentication\Nonce;

interface NonceRepositoryInterface
{
    /**
     * Check if a nonce already exists
     *
     * @param  string $nonce
     * @return boolean
     */
    public function exists($nonce);

    /**
     * Save a nonce
     *
     * @param  string $nonce
     * @return void
     */
    public function save($nonce);

    /**
     * Configure the repository
     *
     * @param  mixed $config
     * @return void
     */
    public function configure($config);
}
