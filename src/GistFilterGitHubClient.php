<?php

/**
 * @file
 * Contains \Drupal\gist_filter\GistFilterGitHubClient.
 */

namespace Drupal\gist_filter;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Implementation of GistFilterClientInterface that requests GitHub api to retrieve a gist.
 */
class GistFilterGitHubClient implements GistFilterClientInterface {

  /**
   * The HTTP client to fetch the gist data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * Constructs a GistFilterGitHubClient instance.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   *
   * @throws GitHubRequestException
   *   When the request to the API fails.
   */
  public function getGist($id) {
    try {
      $url = 'https://api.github.com/gists/' . $id;
      $response = $this->httpClient->request('GET', $url, array('headers' => array('Accept' => 'application/json')));
      $data = (string) $response->getBody();
    }
    catch (RequestException $e) {
      throw new GitHubRequestException(sprintf('"%s" error while requesting %s', $e->getMessage(), $url));
    }

    if (!empty($data)) {
      $gist = json_decode($data, TRUE);
    }
    else {
      $gist = NULL;
    }

    return $gist;
  }

}
