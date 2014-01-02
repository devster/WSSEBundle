<?php

namespace Devster\WSSEBundle\Security\Authentication\Nonce;

class FileNonceRepository implements NonceRepositoryInterface
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function configure($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function save($nonce)
    {
        if (! is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        touch($this->path.'/'.$nonce);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($nonce)
    {
        return file_exists($this->path.'/'.$nonce);
    }
}
