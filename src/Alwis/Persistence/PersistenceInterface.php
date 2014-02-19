<?php
namespace Alwis\Persistence;

interface PersistenceInterface
{
    /**
     * @param $key
     * @return mixed
     */
    public function read($key);

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function write($key,$data);

    /**
     * @param $key
     * @return bool
     */
    public function delete($key);

    /**
     * @param $maxAge
     * @return bool
     */
    public function cleanExpired($maxAge);

    /**
     * @param $key
     * @return bool
     */
    public function exists($key);
}
