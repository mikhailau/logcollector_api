<?php


namespace App\Service;

//use Knp\Component\Pager\PaginatorInterface;
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
        // Query could be a simple query string or DSL syntax based array
        //$query = $this->getArrayQuery($query);

        return $query;
    }


    protected function getFilebeatIndex()
    {
        $response = $this->client->cat()->indices(array('index' => 'filebeat*'));
        return $response[0]['index'] ?? null;;
    }
}