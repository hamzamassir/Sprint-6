<?php

namespace Drupal\article_api\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArticleApiController extends ControllerBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new ArticleApiController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

  /**
   * List specific articles.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the articles.
   */
    public function listArticles()
    {
      // Hardcoded node IDs to retrieve
        $node_ids = [10, 45, 223, 46, 47, 48, 49, 50, 51, 52];

      // Use EntityQuery to load specific nodes
        $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'article')
        ->condition('status', 1)
        ->condition('nid', $node_ids, 'IN')
        ->accessCheck(true);

        $nids = $query->execute();

      // Prepare the response data
        $articles = [];
        foreach ($nids as $nid) {
            $node = $this->entityTypeManager->getStorage('node')->load($nid);

            $articles[] = [
            'nid' => $node->id(),
            'title' => $node->getTitle(),
            ];
        }
        // Create a JSON response with caching
        $response = new CacheableJsonResponse($articles);
        $cache_metadata = new CacheableMetadata();
        $cache_metadata->setCacheMaxAge(3600);

        $node_ex_ids = [10, 45, 223];
        foreach ($node_ex_ids as $nid) {
            $cache_metadata->addCacheTags(['node:' . $nid]);
        }

        $response->addCacheableDependency($cache_metadata);
        return $response;
    }
}
