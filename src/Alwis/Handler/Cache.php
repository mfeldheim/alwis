<?php
namespace Alwis\Handler;

use Alwis\Persistence\PersistenceInterface;

class Cache
{
    /**
     * @var PersistenceInterface
     */
    protected $persistence;

    /**
     * @param PersistenceInterface $persistence
     */
    public function __construct( PersistenceInterface $persistence )
    {
        $this->persistence = $persistence;
    }

    public function __destruct()
    {

    }
}
