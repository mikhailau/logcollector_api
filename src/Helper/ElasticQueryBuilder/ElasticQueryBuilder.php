<?php


namespace App\Helper\ElasticQueryBuilder;


use App\Helper\ElasticQueryBuilder\Interfaces\ElasticQueryBuilderInterface;
use Doctrine\DBAL\Exception;


class ElasticQueryBuilder implements ElasticQueryBuilderInterface
{
    protected $query;
    protected $queryString;
    protected $range;

    protected const QUERY_TEMPLATE = [
        'index' => "",
        'body' => [




            'from' => "",
            'size' => "",
            "sort" => [
                "@timestamp" => [
                    "unmapped_type" => "keyword",
                    "order" => "desc"
                ]
            ],

        ]
    ];

    public function __construct()
    {
        $this->query = self::QUERY_TEMPLATE;
    }

    public function addQueryString($queryString): ElasticQueryBuilderInterface
    {
        switch (true)
        {
            case is_array($queryString):
                $queryString = implode(" OR ", $queryString);
            case is_string($queryString):
                $this->queryString = $queryString;
                break;

            default:
                throw new Exception("query string is wrong", 0);



        }


        return $this;
    }

    public function setPage(int $page): ElasticQueryBuilderInterface
    {
        $this->query['body']['from'] = $page;
        return $this;
    }

    public function setCountPerPage(int $perPage): ElasticQueryBuilderInterface
    {
        $this->query['body']['size'] = $perPage;
        return $this;
    }

    public function getQuery(): array
    {
        $this->compileQuery();
        return $this->query;
    }

    private function compileQuery()
    {
        $this->query['body']['query']['query_string']['query'] ='(' . $this->queryString .')'. ($this->range ? ' AND('.implode(' OR ', $this->range).')':"");

    }

    public function setIndex(string $index): ElasticQueryBuilderInterface
    {
        $this->query['index'] = $index;
        return $this;
    }

    public function addRange($from,$to): ElasticQueryBuilderInterface
    {
        //TODO: add to validation
        $from = $from ?? "1970-01-01";
        $from = new \DateTime($from);
        $from = $from->format('c');
        if($to) {
            $to = new \DateTime($to);
            $to = $to->format('c');
        }
        else $to="now";


        $this->range[] = "@timestamp:[".$from." TO ".$to."]";
        return $this;
    }
}