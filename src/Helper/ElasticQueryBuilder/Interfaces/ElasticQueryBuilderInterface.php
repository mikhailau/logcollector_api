<?php


namespace App\Helper\ElasticQueryBuilder\Interfaces;


interface ElasticQueryBuilderInterface
{
    public function addQueryString(string $query_String): ElasticQueryBuilderInterface;
    public function addRange($from,$to): ElasticQueryBuilderInterface;
    public function setPage(int $page): ElasticQueryBuilderInterface;
    public function setCountPerPage(int $perPage): ElasticQueryBuilderInterface;
    public function setIndex(string $index): ElasticQueryBuilderInterface;
    public function getQuery(): array;
}