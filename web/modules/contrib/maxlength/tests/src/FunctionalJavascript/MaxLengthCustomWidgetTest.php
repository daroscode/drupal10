<?php

namespace Drupal\Tests\maxlength\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the custom widget support.
 *
 * @group maxlength
 */
class MaxLengthCustomWidgetTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'field_ui',
    'maxlength',
    'maxlength_custom_widget_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    EntityFormDisplay::load('node.article.default')
      ->setComponent('body', [
        'type' => 'text_textarea_custom_widget',
        'third_party_settings' => [
          'maxlength' => [
            'maxlength_js' => 200,
            'maxlength_js_label' => 'Content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count',
            'maxlength_js_summary' => 200,
            'maxlength_js_label_summary' => 'Summary content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count',
            'maxlength_js_enforce' => FALSE,
          ],
        ],
      ])
      ->save();
  }

  /**
   * Tests that a custom textarea widget gets picked up and is supported.
   */
  public function testMaxLengthCustomWidgetSupported() {
    $admin_user = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node form display',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $page = $this->getSession()->getPage();
    $page->pressButton('edit-fields-body-settings-edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->checkField('Always show the summary field');
    $page->pressButton('MaxLength Settings');

    // Assert the maxlength config form.
    $this->assertSession()->fieldValueEquals('Summary maximum length', 200);
    $this->assertSession()->fieldValueEquals('Summary count down message', 'Summary content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count');
    $this->assertSession()->fieldValueEquals('Maximum length', 200);
    $this->assertSession()->fieldValueEquals('Count down message', 'Content limited to @limit characters, remaining: <strong>@remaining</strong> and total @count');
    $this->assertSession()->checkboxNotChecked('Hard limit');

    $this->assertSession()->elementsCount('css', '[data-drupal-selector="edit-fields-body-settings-edit-form-third-party-settings-maxlength-maxlength-js-summary"]', 1);

    // Assert the maximum length has to be a positive number.
    $page->fillField('Summary maximum length', '0');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContainsOnce('The maximum length has to be a positive number.');

    // Assert we can unset the value as well.
    $page->fillField('Summary maximum length', '');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextNotContains('The maximum length has to be a positive number.');

    $page->pressButton('body_settings_edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('MaxLength Settings');

    $page->fillField('Summary maximum length', '123');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextNotContains('The maximum length has to be a positive number.');

    $page->pressButton('body_settings_edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('MaxLength Settings');

    $page->fillField('Maximum length', '-1');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContainsOnce('The maximum length has to be a positive number.');

    $page->fillField('Maximum length', '0');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContainsOnce('The maximum length has to be a positive number.');

    // Assert we can unset the value as well.
    $page->fillField('Maximum length', '');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextNotContains('The maximum length has to be a positive number.');

    $page->pressButton('body_settings_edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('MaxLength Settings');

    $page->fillField('Maximum length', '200');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextNotContains('The maximum length has to be a positive number.');

    $page->pressButton('Save');
    $this->assertSession()->responseContains('Maximum summary length: 123');

    $this->drupalGet('node/add/article');

    // Give maxlength.js some time to manipulate the DOM.
    $this->assertSession()->waitForElement('css', 'div.counter');

    // Check each counter for summary and body.
    $this->assertSession()->elementsCount('css', 'div.counter', 2);

    // Check that the counter div follows the description of the field.
    $found = $this->xpath('//textarea[@data-drupal-selector="edit-body-0-value"]/following-sibling::div[@id="edit-body-0-value-counter"]');
    $this->assertCount(1, $found);

    // Fill the summary field with more characters than the limit.
    $page->fillField('Summary', '<strong>Leave blank</strong> to use <u>trimmed value</u> of full text as the summary. Leave blank to use trimmed value of full text as the summary. Extra characters');
    // The counter now should show "-17" for the extra characters.
    $this->assertSession()->pageTextContainsOnce('Summary content limited to 123 characters, remaining: -17 and total 140');

    // Fill the body field with more characters than the limit.
    $page->fillField('Body', '<b>Lorem ipsum</b> dolor sit amet, <u>consectetur adipiscing</u> elit. Ut accumsan justo non interdum fermentum. Phasellus semper risus eu arcu eleifend dignissim. Class aptent taciti sociosqu ad litora erat curae. Extra characters');
    // The counter now should show "-17" for the extra characters.
    $this->assertSession()->pageTextContainsOnce('Content limited to 200 characters, remaining: -17 and total 217');

    // Fill in the title and assert we can save the node with the extra
    // characters, and they are not truncated on edit.
    $page->fillField('Title', 'Article');
    $page->pressButton('Save');

    // Assert the counters in the form again.
    $this->drupalGet('node/1/edit');
    $this->assertSession()->pageTextContainsOnce('Summary content limited to 123 characters, remaining: -17 and total 140');
    $this->assertSession()->pageTextContainsOnce('Content limited to 200 characters, remaining: -17 and total 217');

    // Now set the "Hard limit" option for both of the fields and assert the
    // extra characters are truncated and "Extra characters" string is gone.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $page->pressButton('edit-fields-body-settings-edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('MaxLength Settings');
    $page->checkField('Hard limit');
    $page->pressButton('Update');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('Save');

    $this->drupalGet('node/1/edit');
    $this->assertSession()->pageTextContains('Content limited to 123 characters, remaining: 0');
    $this->assertSession()->pageTextContains('Content limited to 200 characters, remaining: 0');
    $this->assertSession()->pageTextContainsOnce('Summary content limited to 123 characters, remaining: 0 and total 123');
    $this->assertSession()->pageTextContainsOnce('Content limited to 200 characters, remaining: 0 and total 200');
    $this->assertTrue($page->findField('Summary')->getValue() === '<strong>Leave blank</strong> to use <u>trimmed value</u> of full text as the summary. Leave blank to use trimmed value of full text as the summary.');
    $this->assertTrue($page->findField('Body')->getValue() === '<b>Lorem ipsum</b> dolor sit amet, <u>consectetur adipiscing</u> elit. Ut accumsan justo non interdum fermentum. Phasellus semper risus eu arcu eleifend dignissim. Class aptent taciti sociosqu ad litora erat curae.');
  }

}
