<?php

namespace Drupal\webform_xlsx_export\Plugin\WebformExporter;

use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\webform\Plugin\WebformExporter\TabularBaseWebformExporter;
use Drupal\webform\WebformSubmissionInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class XlsxExporter.
 *
 * @package Drupal\webform_xlsx_export\Plugin\WebformExporter
 *
 * @WebformExporter(
 *   id = "xlsx",
 *   label = @Translation("XLSX"),
 *   description = @Translation("Exports results as an Office Open XML file."),
 *   options = FALSE
 * )
 */
class XlsxExporter extends TabularBaseWebformExporter {

  /**
   * PhpSpreadsheet object.
   *
   * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
   */
  private $xls;

  /**
   * {@inheritdoc}
   */
  public function getFileExtension() {
    return 'xlsx';
  }

  /**
   * {@inheritdoc}
   */
  public function createExport() {
    $this->xls = new Spreadsheet();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   */
  public function openExport() {
    $this->xls = IOFactory::load($this->getExportFilePath());
  }

  /**
   * {@inheritdoc}
   *
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */
  public function closeExport() {
    IOFactory::createWriter($this->xls, "Xlsx")->save($this->getExportFilePath());
  }

  /**
   * {@inheritdoc}
   *
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function writeHeader() {
    $sheet = $this->xls->getActiveSheet();

    foreach ($this->buildHeader() as $column => $header) {
      $sheet->setCellValueByColumnAndRow($column + 1, 1, $header);
    }

    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
      ->getFont()
      ->setBold(TRUE);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function writeSubmission(WebformSubmissionInterface $webform_submission) {
    $sheet = $this->xls->getActiveSheet();
    $row = $sheet->getHighestRow();

    foreach ($this->buildRecord($webform_submission) as $column => $record) {
      $valueBinder = NULL;
      if (is_string($record) && strlen($record) > 1 && $record[0] === '=') {
        $valueBinder = new StringValueBinder();
      }
      $sheet->setCellValueByColumnAndRow($column + 1, $row + 1, $record, $valueBinder);
    }
  }

}
