<?php

declare(strict_types = 1);

namespace Drupal\Tests\maxlength\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\link\LinkItemInterface;
use Drupal\Component\Utility\Html;

/**
 * Tests the custom link widget support.
 *
 * @group maxlength
 */
class MaxlengthCustomLinkWidgetTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'field_ui',
    'link',
    'maxlength',
    'maxlength_custom_widget_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The link field name.
   *
   * @var string
   */
  protected $linkFieldName;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'maxlength_link_test', 'name' => 'Link Test']);

    // Create a field with settings to validate.
    $this->linkFieldName = mb_strtolower($this->randomMachineName());
    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->linkFieldName,
      'label' => 'Maxlength Link Field',
      'entity_type' => 'node',
      'type' => 'link',
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'maxlength_link_test',
      'settings' => [
        'title' => DRUPAL_OPTIONAL,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ],
    ]);
    $field->save();

    // Form display.
    EntityFormDisplay::load('node.maxlength_link_test.default')
      ->setComponent($this->linkFieldName, [
        'type' => 'maxlength_link_custom_widget',
        'third_party_settings' => [
          'maxlength' => [
            'maxlength_js' => 40,
            'maxlength_js_label' => 'Content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count',
            'maxlength_js_enforce' => FALSE,
          ],
        ],
      ])
      ->save();
  }

  /**
   * Tests that a custom link widget gets picked up and is supported.
   */
  public function testMaxlengthCustomLinkWidgetSupported(): void {
    $field_html_id = Html::getId($this->linkFieldName);
    $admin_user = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node form display',
    ]);
    $this->drupalLogin($admin_user);

    // Field UI settings.
    $link_settings_maxlength_input_name = 'fields[' . $this->linkFieldName . '][settings_edit_form][third_party_settings][maxlength][maxlength_js]';
    $this->drupalGet('admin/structure/types/manage/maxlength_link_test/form-display');
    $page = $this->getSession()->getPage();
    $page->pressButton('edit-fields-' . $field_html_id . '-settings-edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Open the maxlength settings.
    $page->pressButton('MaxLength Settings');

    // Assert the maxlength settings exist.
    $this->assertSession()->fieldValueEquals('Maximum length', 40);
    $this->assertSession()->fieldValueEquals('Count down message', 'Content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count');
    $this->assertSession()->checkboxNotChecked('Hard limit');
    $this->assertSession()->elementsCount('css', '[name="' . $link_settings_maxlength_input_name . '"]', 1);

    // Set a new limit value.
    $page->findField($link_settings_maxlength_input_name)->setValue("60");
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Save the field display config.
    $page->pressButton('Save');
    $this->assertSession()->responseContains('Maximum length: 60');

    // Create a node.
    $this->drupalGet('node/add/maxlength_link_test');

    // Give maxlength.js some time to manipulate the DOM.
    $this->assertSession()->waitForElement('css', 'div.counter');

    // Check each counter for the link field.
    $this->assertSession()->elementsCount('css', 'div.counter', 1);

    // Check that the counter div follows the description of the field.
    $found = $this->xpath('//input[@name="' . $this->linkFieldName . '[0][title]"]/following-sibling::div[@class="counter"]');
    $this->assertCount(1, $found);
  }

}
