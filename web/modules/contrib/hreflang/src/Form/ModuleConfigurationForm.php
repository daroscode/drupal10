<?php

namespace Drupal\hreflang\Form;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures forms module settings.
 *
 * @phpstan-consistent-constructor
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config_manager, AccessManagerInterface $access_manager) {
    // @phpstan-ignore-next-line Ignore extra parameter on Drupal < 10.2.
    parent::__construct($config_factory, $typed_config_manager);
    $this->accessManager = $access_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('access_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hreflang_admin_settings';
  }

  /**
   * {@inheritdoc}
   *
   * @return string[]
   *   The editable config names.
   */
  protected function getEditableConfigNames() {
    return [
      'hreflang.settings',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed[] $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed[]
   *   The settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hreflang.settings');
    $form['x_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add x-default hreflang tag'),
      '#default_value' => $config->get('x_default'),
      '#description' => $this->t('If enabled, an additional <a href="https://en.wikipedia.org/wiki/Hreflang#X-Default" rel="noreferrer">@html</a> tag will be created.', ['@html' => 'hreflang="x-default"']),
    ];
    $form['x_default_fallback'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Point x-default hreflang tag at the fallback language'),
      '#default_value' => $config->get('x_default_fallback'),
      '#description' => $this->accessManager->checkNamedRoute('language.negotiation_selected') ? $this->t('If enabled, and a <a href=":url">fallback language has been configured</a>, the x-default hreflang tag will point at the fallback language. Otherwise, the x-default hreflang tag will point at the site default language.', [':url' => Url::fromRoute('language.negotiation_selected')->toString()]) : $this->t('If enabled, and a fallback language has been configured, the x-default hreflang tag will point at the fallback language. Otherwise, the x-default hreflang tag will point at the site default language.'),
    ];
    $form['defer_to_content_translation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Defer to Content Translation hreflang tags on content entity pages'),
      '#default_value' => $config->get('defer_to_content_translation'),
      '#description' => $this->t("If enabled, and Content Translation module is enabled, Hreflang module will not add hreflang tags to content entity pages (aside from the x-default tag, if enabled above). As a result, hreflang tags will be added only for languages that have a translation (and to which the user has view access), or for the content's designated language if it is not translatable, although the content could still be accessible under other languages with a translated user interface. Note that Content Translation module does not add query arguments to its hreflang tags, so pages with query arguments will not have a valid set of hreflang tags; this will, however, improve cache efficiency by not creating separate caches for each set of query arguments."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed[] $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('hreflang.settings')
      ->set('x_default', $form_state->getValue('x_default'))
      ->set('x_default_fallback', $form_state->getValue('x_default_fallback'))
      ->set('defer_to_content_translation', $form_state->getValue('defer_to_content_translation'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
