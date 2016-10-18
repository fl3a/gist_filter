<?php

/**
 * @file
 * Contains \Drupal\Tests\gist_filter\Unit\GistFilterCacheDecoratorClientTest.
 */

namespace Drupal\Tests\gist_filter\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\gist_filter\GistFilterCacheDecoratorClient;

/**
 * Tests GistFilterCacheDecoratorClientTest functionality.
 *
 * @coversDefaultClass \Drupal\gist_filter\GistFilterCacheDecoratorClient
 *
 * @group filter
 */
class GistFilterCacheDecoratorClientTest extends UnitTestCase {

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheBackend;

  /**
   * GitHub client mock.
   *
   * @var \Drupal\gist_filter\GistFilterClientInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->cacheBackend = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');
    $this->client = $this->getMock('\Drupal\gist_filter\GistFilterClientInterface');
  }

  /**
   * Test that responses are cached.
   */
  public function testCacheMissProxiesCallToWrappedClientAndCacheTheResponse() {
    $this->client->expects($this->once())
      ->method('getGist')
      ->with('foo')
      ->willReturn('response');

    $this->cacheBackend->expects($this->once())
      ->method('get');

    $this->cacheBackend->expects($this->once())
      ->method('set')
      ->with($this->anything(), 'response');

    $cacheClient = new GistFilterCacheDecoratorClient($this->client, $this->cacheBackend);
    $this->assertEquals('response', $cacheClient->getGist('foo'));

  }

  /**
   * Tests that on cache hit, wrapped client is not invoked.
   */
  public function testIfCachedGistClientIsNotCalled() {
    $this->client->expects($this->never())
      ->method('getGist');

    $this->cacheBackend->expects($this->once())
      ->method('get')
      ->with($this->anything())
      ->willReturn((object) ['data' => 'response']);

    $this->cacheBackend->expects($this->never())
      ->method('set');

    $cacheClient = new GistFilterCacheDecoratorClient($this->client, $this->cacheBackend);
    $this->assertEquals('response', $cacheClient->getGist('foo'));

  }

  /**
   * Tests that consecutive calls with same id use internal cache.
   */
  public function testConsecutiveCallsWithSameId() {
    $this->client->expects($this->never())
      ->method('getGist');

    $this->cacheBackend->expects($this->once())
      ->method('get')
      ->with($this->anything())
      ->willReturn((object) ['data' => 'response']);

    $this->cacheBackend->expects($this->never())
      ->method('set');

    $cacheClient = new GistFilterCacheDecoratorClient($this->client, $this->cacheBackend);
    $cacheClient->getGist('foo');
    $cacheClient->getGist('foo');

  }

}
