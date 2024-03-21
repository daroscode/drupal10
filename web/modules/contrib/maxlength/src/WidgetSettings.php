<?php

namespace Drupal\maxlength;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The widget settings service.
 */
class WidgetSettings implements WidgetSettingsInterface, ContainerInjectionInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs the WidgetSettings service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedSettingsForAll() {
    $settings = [
      'string_textfield' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => TRUE,
      ],
      'string_textarea' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => TRUE,
      ],
      'text_textfield' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => TRUE,
      ],
      'text_textarea' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => TRUE,
      ],
      'text_textarea_with_summary' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => TRUE,
        'truncate_setting' => TRUE,
      ],
      'key_value_textarea' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => TRUE,
        'truncate_setting' => TRUE,
      ],
      'link_default' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => TRUE,
      ],
      'linkit' => [
        'maxlength_setting' => TRUE,
        'summary_maxlength_setting' => FALSE,
        'truncate_setting' => FALSE,
      ],
    ];

    $additional_widget_settings = $this->moduleHandler->invokeAll('maxlength_widget_settings') ?: [];

    return $settings + $additional_widget_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedSettings($widget_plugin_id) {
    $all_settings = $this->getAllowedSettingsForAll();
    if (!empty($all_settings[$widget_plugin_id])) {
      return $all_settings[$widget_plugin_id];
    }
    return [];
  }

}
