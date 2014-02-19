<?php
namespace Alwis\Handler;

use Alwis\Persistence\PersistenceInterface;

class Session extends Cache implements \SessionHandlerInterface
{
    private $sessionId;

    public function __construct( PersistenceInterface $persistenceLayer )
    {
        $this->sessionId = session_id();
        parent::__construct( $persistenceLayer );
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * PHP >= 5.4.0<br/>
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterafce.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function close()
    {
        echo $this->sessionId;
        echo microtime(true) . " [SESS] Closing session\n";
    }

    /**
     * PHP >= 5.4.0<br/>
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterafce.destroy.php
     * @param int $sessionId The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function destroy( $sessionId )
    {
        return $this->persistence->delete( $sessionId );
        echo microtime(true) . " [SESS] Destroying session {$sessionId}\n";
    }

    /**
     * PHP >= 5.4.0<br/>
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterafce.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function gc($maxlifetime)
    {
        echo microtime(true) . " [SESS] Running session garbagecolector, max lifetime: {$maxlifetime}\n";
        return $this->persistence->cleanExpired($maxlifetime);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterafce.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function open($save_path, $session_id)
    {
        echo microtime(true) . " [SESS] Opening session {$session_id}\n";
        return true;
    }


    /**
     * PHP >= 5.4.0<br/>
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function read($session_id)
    {
        echo microtime(true) . " [SESS] Reading from session {$session_id}\n";
        return $this->persistence->read( $session_id );
    }

    /**
     * PHP >= 5.4.0<br/>
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function write($session_id, $session_data)
    {
        echo microtime(true) . " [SESS] Writing to session {$session_id}\n";

        return $this->persistence->write( $session_id, $session_data );
    }

    /**
     * Register myself as PHP session handler
     * @return bool
     * @throws Exception
     */
    public function register()
    {
        echo microtime(true) . " [SESS] Registering session handler\n";

        if ( session_status() === PHP_SESSION_DISABLED ) {
            throw new Exception( 'Sessions must be enabled' );
        }

        if ( session_status() === PHP_SESSION_ACTIVE ) {
            throw new Exception( 'Session already started' );
        }

        /** @noinspection PhpParamsInspection */
        return session_set_save_handler( $this, true );
    }

    /**
     * Start the PHP session
     * @param string|bool $session_id
     * @return bool
     * @throws Exception
     */
    public function start($session_id=false)
    {
        echo microtime(true) . " [SESS] Starting Session\n";
        if ( session_status() === PHP_SESSION_DISABLED ) {
            throw new Exception( 'Sessions must be enabled' );
        }

        if ( session_status() === PHP_SESSION_ACTIVE ) {
            throw new Exception( 'Session already started' );
        }

        if ( false !== $session_id ) {
            session_id( $session_id );
        }

        session_start();
    }
}
