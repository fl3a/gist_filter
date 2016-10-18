<?php

/**
 * @file
 * Tests for the Gist Filter module.
 */

namespace Drupal\gist_filter\Tests;

use Drupal\simpletest\WebTestBase;


/**
 * Test the gist_filter module.
 *
 * @group filter
 */
class GistFilterTestCase extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static $modules = ['filter', 'node', 'gist_filter'];

  protected $user;
  protected $contentType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a content type to test the filters (with default format).
    $this->contentType = $this->drupalCreateContentType();

    // Create and log in our user.
    $this->user = $this->drupalCreateUser(array(
      'create ' . $this->contentType->id() . ' content',
      'administer filters',
    ));
    $this->drupalLogin($this->user);
  }

  /**
   * Testing the embedded gist option.
   */
  public function testEmbedStyle() {
    // Turn on our input filter and set the option to embed.
    $edit = array(
      'filters[gist_filter][status]' => 1,
      'filters[gist_filter][settings][gist_filter_display_method]' => 'embed',
    );

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Hello! [gist:865412]',
    );

    $this->drupalPostForm('node/add/' . $this->contentType->id(), $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertRaw("Hello! ");
    $this->assertRaw('<script src="//gist.github.com/865412.js"></script>');

  }

  /**
   * Testing the embedded gist option with a file parameter.
   */
  public function testEmbedStyleWithFile() {
    // Turn on our input filter and set the option to embed.
    $edit = array(
      'filters[gist_filter][status]' => 1,
      'filters[gist_filter][settings][gist_filter_display_method]' => 'embed',
    );

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Hello! [gist:865412:php_file.php]',
    );

    $this->drupalPostForm('node/add/' . $this->contentType->id(), $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertRaw("Hello! ");
    $this->assertRaw('<script src="//gist.github.com/865412.js?file=php_file.php"></script>');

  }

  /**
   * Testing the link option.
   */
  public function testLinkStyle() {

    // Turn on our input filter and set the option to link.
    $edit = array(
      'filters[gist_filter][status]' => 1,
      'filters[gist_filter][settings][gist_filter_display_method]' => 'link',
    );

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Hello! [gist:865412]',
    );

    $this->drupalPostForm('node/add/' . $this->contentType->id(), $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertRaw('Hello! <a href="http://gist.github.com/865412">http://gist.github.com/865412</a>');

  }

  /**
   * Testing the link option.
   */
  public function testLinkStyleWithFile() {

    // Turn on our input filter and set the option to link.
    $edit = array(
      'filters[gist_filter][status]' => 1,
      'filters[gist_filter][settings][gist_filter_display_method]' => 'link',
    );

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Hello! [gist:865412:php_file.php]',
    );

    $this->drupalPostForm('node/add/' . $this->contentType->id(), $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertRaw('Hello! <a href="http://gist.github.com/865412#file_php_file.php">http://gist.github.com/865412#file_php_file.php</a>');

  }

  /**
   * Testing the code tag option.
   */
  public function testCodeTagStyle() {

    // Turn on our input filter and set the option to code.
    $edit = array(
      'filters[gist_filter][status]' => 1,
      'filters[gist_filter][settings][gist_filter_display_method]' => 'code',
    );

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Hello! [gist:865412]',
    );

    $this->drupalPostForm('node/add/' . $this->contentType->id(), $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertPattern("@<pre type=\"PHP\">(.*)echo(.*)</pre>@sm");
    $this->assertPattern("@<pre type=\"Ruby\">(.*)a = 1\nputs a</pre>@");

  }

}
