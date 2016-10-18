<?php

/**
 * @file
 * Contains \Drupal\gist_filter\GistFilterCacheDecoratorClient.
 */

namespace Drupal\gist_filter;

use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Implementation of that caches the results of the wrapped GistFilterClientInterface injected.
 */
class GistFilterCacheDecoratorClient implements GistFilterClientInterface {

  /**
   * Wrapped GistFilterClientInterface implementation.
   *
   * @var GistFilterClientInterface
   */
  protected $client;

  /**
   * Cache backend.
   *
   * @var CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Array with already processed gists.
   *
   * @var array
   */
  protected $gists = [];

  /**
   * Constructor for proxy implementation.
   *
   * @param GistFilterClientInterface $client
   *   Wrapped client.
   * @param CacheBackendInterface $cache_backend
   *   Cache backend.
   */
  public function __construct(GistFilterClientInterface $client, CacheBackendInterface $cache_backend) {
    $this->client = $client;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * @inheritdoc
   */
  public function getGist($id) {
    // First, try the static cache.
    if (!isset($this->gists[$id])) {
      // Cache ID.
      $cid = 'gist_filter:gist:' . $id;
      // Check if this gist is already in the cache.
      if ($cached = $this->cacheBackend->get($cid)) {
        $gist = $cached->data;
      }
      else {
        $gist = $this->client->getGist($id);
        $this->cacheBackend->set($cid, $gist);
      }

      $this->gists[$id] = $gist;
    }

    return $this->gists[$id];
  }

}
