<?php

namespace Drupal\Tests\hreflang\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for presence of the hreflang link element.
 *
 * @group hreflang
 */
class HreflangTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['hreflang', 'language'];

  /**
   * Functional tests for the hreflang tag.
   */
  public function testHreflangTag(): void {
    global $base_url;
    // User to add language.
    $admin_user = $this->drupalCreateUser([
      'administer languages',
      'access administration pages',
      'administer site configuration',
    ]);
    $this->drupalLogin($admin_user);
    // Add predefined language.
    $this->drupalGet('admin/config/regional/language/add');
    $edit = ['predefined_langcode' => 'fr'];
    $this->submitForm($edit, 'Add language');

    // Setup a new user with cached hreflang tags.
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);
    $this->drupalGet('user/3');
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/user/3?check_logged_in=1" />');
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/user/3?check_logged_in=1" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/user/3" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/user/3" />');

    $this->drupalLogin($admin_user);
    $this->drupalGet('admin');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/admin" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/admin" />');
    $this->drupalGet('fr/admin');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/admin" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin" />');

    // Disable x-default hreflang tag.
    $this->drupalGet('admin/config/search/hreflang');
    $edit = ['x_default' => FALSE];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('admin');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin" />');
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/admin" />');

    // Disable URL detection and enable session detection.
    $this->drupalGet('admin/config/regional/language/detection');
    $edit = [
      'language_interface[enabled][language-url]' => FALSE,
      'language_interface[enabled][language-session]' => '1',
    ];
    $this->submitForm($edit, 'Save settings');

    $this->drupalGet('admin');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/admin?language=fr" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin" />');
    $this->drupalGet('admin', ['query' => ['language' => 'en']]);
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/admin?language=fr" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin?language=en" />');
    $this->drupalGet('admin', ['query' => ['language' => 'fr']]);
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/admin?language=fr" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/admin?language=en" />');

    // Configure a fallback language and re-enable the x-default tag.
    $this->drupalGet('admin/config/regional/language/detection/selected');
    $edit = ['selected_langcode' => 'fr'];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('admin/config/search/hreflang');
    $edit = ['x_default' => TRUE];
    $this->submitForm($edit, 'Save configuration');

    // The x-default tag should point at the fallback language.
    $this->drupalGet('admin', ['query' => ['language' => 'en']]);
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/admin?language=fr" />');

    $this->drupalGet('admin/config/search/hreflang');
    $edit = ['x_default_fallback' => FALSE];
    $this->submitForm($edit, 'Save configuration');

    // The x-default tag should point at the default language.
    $this->drupalGet('admin', ['query' => ['language' => 'fr']]);
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="x-default" href="' . $base_url . '/admin?language=en" />');
  }

}
