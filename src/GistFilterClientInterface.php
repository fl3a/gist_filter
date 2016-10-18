<?php

/**
 * @file
 * Contains \Drupal\gist_filter\GistFilterClientInterface.
 */

namespace Drupal\gist_filter;

/**
 * Interface for client implementations.
 */
interface GistFilterClientInterface {

  /**
   * Retrieves a gist from its identifier.
   *
   * @param string $id
   *   The gist identifier.
   *
   * @return array
   *   An array representing the gist.
   */
  public function getGist($id);

}
