<?php

/**
 * @file
 * Contains \Drupal\Tests\gist_filter\Unit\GistFilterGitHubClientTest.
 */

namespace Drupal\Tests\gist_filter\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\gist_filter\GistFilterGitHubClient;
use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Tests GistFilterGitHubClient functionality.
 *
 * @coversDefaultClass \Drupal\gist_filter\GistFilterGitHubClient
 *
 * @group filter
 */
class GistFilterGitHubClientTest extends UnitTestCase {

  /**
   * Test that our API retrieval function caches calls to the Github API.
   */
  public function testGistFilterGitHubClientRequestsToGitHubApi() {
    $response = $this->getMockBuilder('\Psr\Http\Message\ResponseInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $response->expects($this->once())
      ->method('getBody');

    $httpClient = $this->getMockBuilder('\GuzzleHttp\ClientInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $httpClient->expects($this->once())
      ->method('request')
      ->will($this->returnValue($response));

    $httpClientFactory = $this->getMockBuilder('\Drupal\Core\Http\ClientFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $httpClientFactory->expects($this->once())
      ->method('fromOptions')
      ->will($this->returnValue($httpClient));

    $client = new GistFilterGitHubClient($httpClientFactory);
    $client->getGist('foo');

  }

  /**
   * @expectedException \Drupal\gist_filter\GitHubRequestException
   */
  public function testGistFilterGitHubClientThrowsExceptionIfCommunicationWithApiFails() {
    $httpClient = $this->getHttpClient([
      new RequestException('', $this->getMock('\Psr\Http\Message\RequestInterface')),
    ]);

    $client = new GistFilterGitHubClient($httpClient);
    $client->getGist('foo');

  }

  /**
   * Test that returned json data is converted into an array.
   */
  public function testWeCanReturnGistAsAnArray() {
    $httpClient = $this->getHttpClient([
      new GuzzleResponse(200, [], '{"foo": "bar"}'),
    ]);

    $client = new GistFilterGitHubClient($httpClient);
    $result = $client->getGist('foo');

    $this->assertEquals(["foo" => "bar"], $result);

  }

  /**
   * Generates a Drupal\Core\Http\ClientFactory with the mocked responses.
   *
   * @param array $responses
   *   Collection of responses returned by the client.
   *
   * @return \Drupal\Core\Http\ClientFactory'
   */
  protected function getHttpClient(array $responses = []) {
    if (empty($responses)) {
      $responses = [new GuzzleResponse(200, [], '{}')];
    }

    $this->mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($this->mock);
    $guzzle = new GuzzleClient(array('handler' => $handlerStack));

    $httpClientFactory = $this->getMockBuilder('\Drupal\Core\Http\ClientFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $httpClientFactory->expects($this->any())
      ->method('fromOptions')
      ->will($this->returnValue($guzzle));

    return $httpClientFactory;
  }

}
