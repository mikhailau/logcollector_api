<?php

namespace App\Controller;


use App\Helper\ElasticQueryBuilder\ElasticQueryBuilder;

use App\Service\ElasticService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use \sspat\ESQuerySanitizer\Sanitizer;
use Elasticsearch\ClientBuilder;

class ApiController extends AbstractController
{



    /**
     * @Route("/api/indices", methods={"GET"})
     */
    public function handleGetApi()
    {
        $client = ClientBuilder::create()->build();
        $response = $client->cat()->indices(array('index' => 'filebeat*'));
        return $this->json($response, 200);
    }

    /**
     * @Route("/api/search", name="search", methods={"POST"})
     */
    public function searchData(
        Request $request,
        ElasticService $elasticSearch,
        ValidatorInterface $validator

    )
    {


        $emailConstraint = new Assert\Email();
        // все "опции" ограничения могут быть установлены таким образом
        $emailConstraint->message = 'Invalid email address';
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search');
        $timestamp = $request->get('timestamp', null);


        $input = ['page' => $page, 'perPage' => $perPage, 'search' => $search];

        $constraints = new Assert\Collection([
            'page' => [new Assert\Positive()],
            'perPage' => [new Assert\Choice(["10", "25", "50", "100"]), new Assert\Positive()],
            'search' => [new Assert\NotNull],
        ]);


        $validationResult = $this->validateRequest($validator, $constraints, $input);
        if ($validationResult) {
            return $validationResult;
        }
        $excludeCharacters = ['!', '^', '[', ']', '{', '}', '.', '/', '(', ')', '-', '*', '+'];
        if (is_array($search)) {
            array_walk($search, function (&$item) use ($excludeCharacters) {
                $item = Sanitizer::escape($item, $excludeCharacters);

            });
            foreach ($search as $item) {
                $this->sanitizeInjections($item);
            }
        } else {
            $search = Sanitizer::escape($search, $excludeCharacters);
            $this->sanitizeInjections($search);
        }



        $query = new ElasticQueryBuilder();
        try {
            $query->addQueryString($search)
                ->setPage($page)
                ->setCountPerPage($perPage);
            if (is_array($timestamp)) {
                foreach ($timestamp as $item) {
                    $query->addRange($item['from'] ?? null, $item['to'] ?? null);
                }
            }
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }

        $list = $elasticSearch->getPaginatedList($query);

        if (!$list['_shards']['successful'] ?? false) {
            return $this->getError('Elastic request error');
        }

        $total = $list['hits']['total']['value'];
        $list = $list['hits']['hits'] ?? [];


        $result = array_map(function ($x) {
            return $x['_source'];
        }, $list);

        return new JsonResponse([
            'status' => 'success',
            'total' => $total,
            'data' => [$result],
            'message' => null
        ],
            200);


    }


    private function validateRequest($validator, $constraints, $input)
    {
        $errorList = $validator->validate(
            $input,
            $constraints
        );

        if (0 === count($errorList)) {
            return null;
        } else {
            $errorMessage = $errorList[0]->getMessage();
            $field = $errorList[0]->getPropertyPath();
            return $this->getError($field . ": " . $errorMessage);

        }
    }


    private function getError(string $message, int $statusCode = 400): JsonResponse
    {
        return new JsonResponse([
            "status" => "error",
            "data" => null, /* or optional error payload */
            "message" => $message

        ], $statusCode);
    }


    private function sanitizeInjections($searchString)
    {
        //Todo: this is attempt for filtering injections, but need to check elastic injections wordlist
        $pairs = [
            ["[", "]"],
            ["{", "}"],
            ["(", ")"]
        ];
        foreach ($pairs as $pair)
            if (
                (substr_count($searchString, $pair[0]) !== substr_count($searchString, $pair[1]))
                || (substr_count($searchString, '/') % 2 === 0)
            ) {
                $this->getError("Faild");
            }


    }


}
