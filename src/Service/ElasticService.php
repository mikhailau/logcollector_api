<?php


namespace App\Service;

use App\Helper\ElasticQueryBuilder\Interfaces\ElasticQueryBuilderInterface;
use Elasticsearch\ClientBuilder;

class ElasticService
{


    private $client;

    public function __construct()
    {
        $this->setClient();
    }

    protected function setClient()
    {
        $hosts = [
            'localhost:9200',
        ];
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }

    //public function getPaginatedList($query, $page, $size = 10)
    public function getPaginatedList(ElasticQueryBuilderInterface $query)
    {
        $query->setIndex($this->getFilebeatIndex());

        $results = $this->client->search($query->getQuery()); // Search response is here
        return $results;
    }


    protected function getQuery($query)
    {
        return $query;
    }


    protected function getFilebeatIndex()
    {
        if(isset ($_ENV['INDEX']))
            return $_ENV['INDEX'];
        $response = $this->client->cat()->indices(array('index' => 'filebeat*'));
        return $response[0]['index'] ?? null;
    }
}