<?php
ob_start();
require_once __DIR__ . '/../../vendor/autoload.php';

$session = new \Alwis\Handler\Session(
    new \Alwis\Persistence\SimpleDb([
        'key' => 'xxx',
        'secret' => 'xxx',
        'region' => \Aws\Common\Enum\Region::US_EAST_1,
        'domain' => 'PHPSESSION'
    ])
);

ini_set( 'session.gc_probability', 1 );
ini_set( 'session.gc_divisor', 1 );

echo microtime(true) . " [BIN] Register Session\n";
$session->register();
echo microtime(true) . " [BIN] Start Session\n";
$session->start('n6jtbs4ntl520tm6143e4r4hk3');

echo microtime(true) . " [BIN] Read session data\n";
print_r( $_SESSION );

echo microtime(true) . " [BIN] Add key 'a' to Session\n";

$_SESSION['a'] = array('time' => time() );

echo microtime(true) . " [BIN] Add key 'b' to Session\n";
$_SESSION['b'] = array('time' => time() );

echo microtime(true) . " [BIN] Add key 'c' to Session\n";
$_SESSION['c'] = array('time' => time() );

/*unset( $_SESSION['a'] );
unset( $_SESSION['b'] );
unset( $_SESSION['c'] );*/

echo microtime(true) . " [BIN] Close Session\n";
//$session->close();
//session_destroy();






ob_end_flush();