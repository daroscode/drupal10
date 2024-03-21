<?php

namespace Drupal\Tests\webform_xlsx_export\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Drupal\webform_xlsx_export\Plugin\WebformExporter\XlsxExporter;
use Psr\Log\LoggerInterface;

/**
 * Class XlsxExporterTest.
 *
 * @package Drupal\Tests\webform_xlsx_export\Unit
 *
 * @group webform_xlsx_export
 */
class XlsxExporterTest extends UnitTestCase {

  /**
   * Exporter class used in tests.
   *
   * @var \Drupal\webform_xlsx_export\Plugin\WebformExporter\XlsxExporter
   */
  private $exporter;

  /**
   * Prepare variables used in tests.
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $this->createMock(LoggerInterface::class);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->createMock(ConfigFactoryInterface::class);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);

    /** @var \Drupal\webform\Plugin\WebformElementManagerInterface $element_manager */
    $element_manager = $this->createMock(WebformElementManagerInterface::class);

    /** @var \Drupal\webform\WebformTokenManagerInterface $token_manager */
    $token_manager = $this->createMock(WebformTokenManagerInterface::class);

    $this->exporter = new XlsxExporter(
      [],
      'foo',
      NULL,
      $logger,
      $config_factory,
      $entity_type_manager,
      $element_manager,
      $token_manager
    );
  }

  /**
   * Test getFileExtension().
   */
  public function testGetFileExtension() {
    $this->assertEquals('xlsx', $this->exporter->getFileExtension());
  }

  /**
   * Test createExport().
   */
  public function testCreateExport() {
    $this->assertNull($this->exporter->createExport());
  }

  /**
   * Test openExport().
   */
  public function testOpenExport() {
    $this->markTestIncomplete('We need to mock ConfigFactoryInterface.');
  }

  /**
   * Test closeExport().
   */
  public function testCloseExport() {
    $this->markTestIncomplete('We need to mock ConfigFactoryInterface.');
  }

}
