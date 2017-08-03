<?php

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    protected $credentials = [];

    /** @var \MCS\MWSClient $client */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $config = $this->getConfig();

        $this->setCredentials($config);

        $this->setClient($this->credentials);
    }

    private function getConfig(): array
    {
        $configFile = __DIR__ . '/config.ini';
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile);
        } else {
            throw new \Exception("Please create config.ini file first!");
        }

        return $config;
    }

    public function setCredentials($config)
    {
        try {
            $this->credentials = [
                'Marketplace_Id' => $config['marketplace_id'],
                'Access_Key_ID' => $config['access_key'],
                'Secret_Access_Key' => $config['secret_access_key'],
                'Seller_Id' => $config['seller_id'],
                'MWSAuthToken' => $config['mws_auth_token'],
            ];
        } catch (\Exception $exception) {
            throw new Exception('Please enter your credentials into config.ini file!');
        }
    }

    public function setClient($credentials)
    {
        $this->client = new \MCS\MWSClient($credentials);
        return $this->client;
    }

    public function getClient(): \MCS\MWSClient
    {
        return $this->client;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }
}