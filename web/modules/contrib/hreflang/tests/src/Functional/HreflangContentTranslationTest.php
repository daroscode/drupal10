<?php

namespace Drupal\Tests\hreflang\Functional;

use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * Tests for presence of the hreflang link element.
 *
 * @group hreflang
 */
class HreflangContentTranslationTest extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * @var string[]
   */
  protected static $modules = ['hreflang', 'content_translation'];

  /**
   * Functional tests for the hreflang tag.
   */
  public function testHreflangTag(): void {
    global $base_url;
    // User to add language.
    $admin_user = $this->drupalCreateUser([
      'administer languages',
      'administer site configuration',
      'create page content',
      'administer content types',
      'translate any entity',
    ]);
    $this->drupalLogin($admin_user);
    // Add predefined language.
    $this->drupalGet('admin/config/regional/language/add');
    $edit = ['predefined_langcode' => 'fr'];
    $this->submitForm($edit, 'Add language');
    // Enable translation for a page content type.
    \Drupal::service('content_translation.manager')->setEnabled('node', 'page', TRUE);
    $this->drupalGet('admin/structure/types/manage/page');
    $edit = ['language_configuration[language_alterable]' => TRUE];
    try {
      // Drupal 10.1 and earlier.
      $this->submitForm($edit, 'Save content type');
    }
    catch (ElementNotFoundException $e) {
      // Drupal 10.2 and later.
      $this->submitForm($edit, 'Save');
    }

    // Add node.
    $this->drupalGet('node/add/page');
    $edit = ['title[0][value]' => 'Test front page'];
    $this->submitForm($edit, 'Save');
    // Set front page.
    $this->drupalGet('admin/config/system/site-information');
    $edit = ['site_frontpage' => '/node/1'];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('');
    // English hreflang found on English page.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/" />');
    // French hreflang found on English page.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr" />');
    // English hreflang found on English page.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/node/1" />');
    // French hreflang found on English page.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/node/1" />');
    $this->drupalGet('fr');
    // English hreflang found on French page.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/" />');
    // French hreflang found on French page.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr" />');
    // English hreflang found on French page.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/node/1" />');
    // French hreflang found on French page.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/node/1" />');

    // Enable the "Defer to Content Translation hreflang tags on content entity
    // pages" option.
    $this->drupalGet('admin/config/search/hreflang');
    $edit = ['defer_to_content_translation' => TRUE];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('');
    // French hreflang shouldn't be set as node doesn't have corresponding
    // translation.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr" />');
    // Make sure that x-default points to the en version of the node.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/" />');

    // Translate node to the french language.
    $this->drupalGet('node/1/translations/add/en/fr');
    $edit = ['title[0][value]' => 'FR: Test front page'];
    $this->submitForm($edit, 'Save (this translation)');

    $this->drupalGet('fr');
    // Node should have both hreflangs and x-default that points to "/".
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/" />');
    // Make sure that x-default hreflang points to the source translation.
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/" />');
    // Disable x-default configuration option.
    $this->drupalGet('admin/config/search/hreflang');
    $edit = ['x_default' => FALSE];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('/admin');
    // Make sure that x-default link not added.
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/" />');
  }

}
