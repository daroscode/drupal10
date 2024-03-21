<?php

namespace Drupal\Tests\maxlength\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests Javascript behaviour of MaxLength module.
 *
 * @group maxlength
 */
class MaxLengthJavascriptTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test', 'maxlength', 'text', 'link'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that a single maxlength message is displayed to a formatted textarea.
   */
  public function testMaxLengthIsUnique() {
    FieldStorageConfig::create([
      'type' => 'text_long',
      'entity_type' => 'entity_test',
      'field_name' => 'foo',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => 'foo',
      'label' => 'Foo',
      'description' => 'Description of a text field',
    ])->save();
    $widget = [
      'type' => 'text_textarea_with_summary',
      'settings' => [
        'show_summary' => TRUE,
        'summary_rows' => 3,
      ],
      'third_party_settings' => [
        'maxlength' => [
          'maxlength_js' => 200,
          'maxlength_js_label' => 'Content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count',
        ],
      ],
    ];
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent('foo', $widget)
      ->save();

    $entity = EntityTest::create(['type' => 'entity_test', 'name' => 'Test']);
    $entity->save();

    $this->drupalLogin($this->drupalCreateUser(['administer entity_test content']));
    $this->drupalGet($entity->toUrl('edit-form'));

    // Assert the maxlength counter labels.
    $this->assertSession()->pageTextContainsOnce('Content limited to 200 characters, remaining: 200 and total 0');

    // Give maxlength.js some time to manipulate the DOM.
    $this->assertSession()->waitForElement('css', 'div.counter');

    // Check that only a counter div is found on the page.
    $this->assertSession()->elementsCount('css', 'div.counter', 1);

    // Check that the counter div follows the description of the field.
    $found = $this->xpath('//div[@data-drupal-selector="edit-foo-0"]/following-sibling::div[@id="edit-foo-0-value-counter"]');
    $this->assertCount(1, $found);

    // Add some text to the field and assert the maxlength counters changed
    // accordingly.
    $this->getSession()->getPage()->fillField('Foo', 'Some text with <strong>html</strong>');

    $this->assertSession()->pageTextContainsOnce('Content limited to 200 characters, remaining: 181 and total 19');
  }

  /**
   * Tests the JS enforce doesn't allow the user to type further than the limit.
   */
  public function testMaxLengthJsEnforce() {
    FieldStorageConfig::create([
      'type' => 'link',
      'entity_type' => 'entity_test',
      'field_name' => 'link',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => 'link',
      'label' => 'Link',
      'description' => 'Description of a text field',
    ])->save();
    FieldStorageConfig::create([
      'type' => 'string',
      'entity_type' => 'entity_test',
      'field_name' => 'foo',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => 'foo',
      'label' => 'Foo',
      'description' => 'Description of a text field',
    ])->save();
    $text_widget = [
      'type' => 'string_textfield',
      'third_party_settings' => [
        'maxlength' => [
          'maxlength_js' => 5,
          'maxlength_js_enforce' => TRUE,
        ],
      ],
    ];
    $link_widget = [
      'type' => 'link_default',
      'third_party_settings' => [
        'maxlength' => [
          'maxlength_js' => 5,
          'maxlength_js_enforce' => TRUE,
        ],
      ],
    ];
    EntityFormDisplay::load('entity_test.entity_test.default')
      ->setComponent('foo', $text_widget)
      ->setComponent('link', $link_widget)
      ->save();

    $entity = EntityTest::create(['type' => 'entity_test', 'name' => 'Test']);
    $entity->save();

    $this->drupalLogin($this->drupalCreateUser(['administer entity_test content']));
    $this->drupalGet($entity->toUrl('edit-form'));

    // Insert a string longer than the limit in the given field.
    $this->getSession()->getPage()->fillField('Foo', 'String');
    $this->getSession()->getPage()->fillField('Link text', 'String');
    $this->assertTrue($this->getSession()->getPage()->findField('Foo')->getValue() === 'Strin');
    $this->assertTrue($this->getSession()->getPage()->findField('Link text')->getValue() === 'Strin');
  }

}
