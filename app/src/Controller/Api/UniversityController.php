<?php

namespace App\Controller\Api;

use Pimcore\Model\DataObject\University;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/universities")
 */
class UniversityController
{
    /**
     * GET /api/universities
     * Lista tutte le università, con filtro opzionale per country
     *
     * Esempi:
     *   /api/universities
     *   /api/universities?country=GB
     *   /api/universities?limit=10&offset=0
     *   /api/universities/{id}
     */
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $listing = new University\Listing();
        $listing->setUnpublished(false);
        $listing->setOrderKey('name');
        $listing->setOrder('ASC');

        // Filtro per country
        $country = $request->query->get('country');
        if ($country) {
            $listing->setCondition('country = :country', ['country' => $country]);
        }

        // Paginazione
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);
        $listing->setLimit($limit);
        $listing->setOffset($offset);

        $results = [];
        foreach ($listing as $university) {
            $results[] = $this->serialize($university);
        }

        return new JsonResponse([
            'total' => $listing->getTotalCount(),
            'limit' => $limit,
            'offset' => $offset,
            'data' => $results,
        ]);
    }

    /**
     * GET /api/universities/{id}
     * Dettaglio di una singola università
     */
    #[Route('/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail(int $id): JsonResponse
    {
        $university = University::getById($id);

        if (!$university || !$university->isPublished()) {
            return new JsonResponse(['error' => 'University not found'], 404);
        }

        return new JsonResponse($this->serialize($university));
    }

    /**
     * Serializza un oggetto University in array
     */
    private function serialize(University $university): array
    {
        // Estrai le URL dal block
        $urls = [];
        $urlsBlock = $university->getUrls();
        if ($urlsBlock) {
            foreach ($urlsBlock as $block) {
                if (isset($block['url'])) {
                    $urls[] = $block['url']->getData();
                }
            }
        }

        return [
            'id' => $university->getId(),
            'name' => $university->getName(),
            'country' => $university->getCountry(),
            'urls' => $urls,
        ];
    }
}
