<?php

namespace UnicaenAuth\Authentication\Storage;

use UnicaenAuth\Authentication\Storage\ChainEvent;

interface ChainableStorage
{
    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws InvalidArgumentException If reading contents from storage is impossible
     * @return People
     */
    public function read(ChainEvent $e);
    
    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws InvalidArgumentException If writing $contents to storage is impossible
     * @return void
     */
    public function write(ChainEvent $e);

    /**
     * Clears contents from storage
     *
     * @throws InvalidArgumentException If clearing contents from storage is impossible
     * @return void
     */
    public function clear(ChainEvent $e);
}