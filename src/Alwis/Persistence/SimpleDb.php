<?php
namespace Alwis\Persistence;

use Alwis\Handler\Exception;
use Aws\SimpleDb\Exception\NoSuchDomainException;
use Aws\SimpleDb\SimpleDbClient;

class SimpleDb implements PersistenceInterface
{
    /**
     * @var \Aws\SimpleDb\SimpleDbClient
     */
    private $client;
    private $domain;

    /**
     * @param $config
     * @throws Exception
     */
    function __construct( $config )
    {
        echo microtime(true) . " [SDB] Constructor call\n";
        if ( !array_key_exists( 'domain', $config ) ) {
            throw new Exception(__CLASS__ . '::construct config requires a key "domain"' );
        }
        $this->domain = $config['domain'];
        unset( $config['domain'] );

        $this->client = SimpleDbClient::factory($config);

        try {
            echo microtime(true) . " [SDB] Reading domain {$this->domain}\n";
            $this->client->domainMetadata( ['DomainName'=>$this->domain] );
        } catch( NoSuchDomainException $e ) {
            echo microtime(true) . " [SDB] Creating domain {$this->domain}\n";
            $this->client->createDomain( ['DomainName'=>$this->domain] );
        }
    }

    function __destruct()
    {}

    /**
     * @param $key
     * @return mixed
     */
    public function read($key)
    {
        echo microtime(true) . " [SDB] Reading key {$key}\n";

        $result = $this->client->getAttributes([
            'DomainName' => $this->domain,
            'ItemName' => $key,
            'Attributes' => [
                'data'
            ]
        ]);

        if ( $result->hasKey('Attributes') ) {
            $attributes = $result->get('Attributes');
            if ( ( sizeof( $attributes ) == 2 ) and array_key_exists('Name', $attributes[1] ) and $attributes[1]['Name'] == 'data' ) {
                return $attributes[1]['Value'];
            }
        }
        return false;
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function write($key, $data)
    {
        echo microtime(true) . " [SDB] Writing key {$key}\n";

        if ( !is_string( $data ) ) {
            $data = serialize( $data );
        }

        $attributes = [
            'DomainName' => $this->domain,
            'ItemName' => $key
        ];

        if ( $data ) {
            $attributes['Attributes'] = [
                ['Name' => 'data', 'Value' => $data,'Replace' => true],
                ['Name' => 'timestamp', 'Value' => time(),'Replace' => true]
            ];
            $this->client->putAttributes($attributes);
        } else {
            $this->client->deleteAttributes( $attributes );
        }
        return true;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        echo microtime(true) . " [SDB] Deleting key {$key}\n";

        $this->client->deleteAttributes([
            'DomainName' => $this->domain,
            'ItemName' => $key
        ]);

        return true;
    }

    public function cleanExpired($maxAge)
    {
        $timestamp = time() - $maxAge;

        echo microtime(true) . " [SDB] Cleaning expired items older than " . date( 'Y-m-d H:i:s', $timestamp ) . "\n";
        echo microtime(true) . " [SDB] select * from {$this->domain} where timestamp <= '{$timestamp}'\n";

        $iterator = $this->client->getIterator('Select', [
            'SelectExpression' => "select * from {$this->domain} where timestamp <= '{$timestamp}'"
        ]);

        $batch = [
            'DomainName' => $this->domain,
            'Items' => []
        ];

        $bc=0;
        foreach( $iterator as $item ) {
            $batch['Items'][$bc] = ['Name'=>$item['Name']];
            $bc++;
            if ( sizeof( $batch['Items'] ) == 25 ) {

                echo microtime(true) . " [SDB] Delete batch \n";

                $this->client->batchDeleteAttributes( $batch );
                $batch['Items'] = [];
                $bc=0;
            }
        }

        if ( sizeof( $batch['Items'] ) ) {
            $this->client->batchDeleteAttributes( $batch );
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        echo microtime(true) . " [SDB] Checking key {$key}\n";
    }
}
