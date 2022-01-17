<?php

namespace Drupal\hbt_log_Manager\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure hbt_log_Manager settings for this site.
 */
class ConfigForm extends ConfigFormBase {

  private $configuration;

  /**
   * Gets the configuration names that will be editable.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *
   * @return void An array of configuration object names that are editable if
   *   called in An array of configuration object names that are editable if
   *   called in conjunction with the trait's config() method.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configuration = $this->config('hbt_log_Manager.settings');
    parent::__construct($config_factory);
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId(): string {
    return 'hbt_log_Manager_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): ?array {
    /** First Attribution Cookie **/
    $form['Enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Logger Handler'),
      '#description' => $this->t('If you want to add our logger handler to monolog and send all log to GrayLog Or watchDog table, use this module to do this'),
      '#default_value' => $this->configuration->get('Enable'),
      '#required' => FALSE,
    ];
    $form['Log_aggregation_for_Development_Environment'] = [
      '#tree' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Log Aggregation For Development Environment'),
      '#description' => $this->t('Set the Log properties for development Environment'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['Log_aggregation_for_Development_Environment']['default_logger'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Logger'),
      '#description' => $this->t('Here you can select which one of [DB,Graylog] shall use as default logger , this option can change only in code by developer'),
      '#default_value' => $this->configuration->get('Log_aggregation_for_Development_Environment.default_logger'),
      '#options' => [
        'Graylog' => 'GrayLog',
        'DB' => 'DB',
      ],
      '#required' => TRUE,
    ];
    $form['Log_aggregation_for_Test_Environment'] = [
      '#tree' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Log Aggregation For Test Environment'),
      '#description' => $this->t('Set the Log properties for Test Environment'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['Log_aggregation_for_Test_Environment']['default_logger'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Logger'),
      '#description' => $this->t('Here you can select which one of [DB,Graylog] shall use as default logger , this option can change only in code by developer'),
      '#default_value' => $this->configuration->get('Log_aggregation_for_Test_Environment.default_logger'),
      '#options' => [
        'Graylog' => 'GrayLog',
        'DB' => 'DB',
      ],
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    $form['Log_aggregation_for_Live_Environment'] = [
      '#tree' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Log Aggregation For Live Environment'),
      '#description' => $this->t('Set the Log properties for Live Environment'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['Log_aggregation_for_Live_Environment']['default_logger'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Logger'),
      '#description' => $this->t('Here you can select which one of [DB,Graylog] shall use as default logger'),
      '#default_value' => $this->configuration->get('Log_aggregation_for_Live_Environment.default_logger'),
      '#options' => [
        'Graylog' => 'GrayLog',
        'DB' => 'DB',
      ],
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (($form_state->getValue([
        'Log_aggregation_for_Live_Environment',
        'default_logger',
      ])) !== 'GrayLog') {
      $form_state->setError($form['Log_aggregation_for_Live_Environment']['default_logger'], 'for live version you should enable log into graylog');
    }
    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $enable = $form_state->getValue('Enable');
    $logIntoGrayLogInDev = $form_state->getValue([
      'Log_aggregation_for_Development_Environment',
      'default_logger',
    ]);
    $logIntoGrayLogInTest = $form_state->getValue([
      'Log_aggregation_for_Test_Environment',
      'default_logger',
    ]);
    $logIntoGrayLogInLive = $form_state->getValue([
      'Log_aggregation_for_Live_Environment',
      'default_logger',
    ]);
    /**
     * Set Enabled status for exception handler
     */
    $this->configuration->set('Enable', (bool) $enable)->save();
    /**
     * Log for development environment
     */
    $this->configuration->set('Log_aggregation_for_Development_Environment.default_logger', $logIntoGrayLogInDev)
      ->save();
    /**
     * Log for test environment
     */
    $this->configuration->set('Log_aggregation_for_Test_Environment.default_logger', $logIntoGrayLogInTest)
      ->save();
    /**
     * Log for Live environment
     */
    $this->configuration->set('Log_aggregation_for_Live_Environment.default_logger', $logIntoGrayLogInLive)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames(): ?array {
    return ['hbt_log_Manager.settings',];
  }

}
