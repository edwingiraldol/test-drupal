<?php

declare(strict_types=1);

namespace Drupal\test_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Configure Test Product settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   *
   * Returns the form ID for the settings form.
   */
  public function getFormId(): string {
    return 'test_product_settings';
  }

  /**
   * {@inheritdoc}
   *
   * Returns the names of the configuration objects that can be edited.
   */
  protected function getEditableConfigNames(): array {
    return ['test_product.settings'];
  }

  /**
   * {@inheritdoc}
   *
   * Builds the configuration form.
   *
   * @param array $form
   *   The form definition array for the settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    // Get the mailer service that handles highlighted product notifications.
    $service = \Drupal::service('test_product.highlighted_product_mailer');

    // Option to select all admin users.
    $user_options['all'] = $this->t('All Admin Users');
    $users = $service->getAdminUsers();
    foreach ($users as $user) {
      $user_options[$user->id()] = $user->getDisplayName();
    }

    // Form element to select which admin users to send mail to.
    $form['container']['admins_to_send_mail'] = [
      '#type' => 'select',
      '#title' => $this->t('Select users to send mail'),
      '#multiple' => TRUE,
      '#required' => TRUE,
      '#options' => $user_options,
      '#default_value' => $this->config('test_product.settings')->get('admins_to_send_mail'),
    ];

    // Form element to set the amount of products to send.
    $form['container']['amount_of_products'] = [
      '#type' => 'number',
      '#required' => TRUE,
      '#title' => $this->t('Amount of products to send'),
      '#default_value' => $this->config('test_product.settings')->get('amount_of_products'),
    ];

    // Submit button for the configuration form.
    $form['container']['actions']['#type'] = 'actions';
    $form['container']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   *
   * Handles form submission.
   *
   * Saves the form configuration and updates the settings based on user input.
   *
   * @param array &$form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected_admins = $form_state->getValue('admins_to_send_mail');

    // Check if 'all' option is selected, update selected admins to include all admins.
    if (in_array('all', $selected_admins)) {

      // Retrieve all admin user IDs.
      $query = \Drupal::entityQuery('user')
        ->accessCheck(FALSE)
        ->condition('status', 1)
        ->condition('roles', 'administrator');
      $all_admin_uids = $query->execute();
      $selected_admins = array_keys($all_admin_uids); // Update the selected to all admins
    }

    // Save the configuration settings.
    $this->config('test_product.settings')
      ->set('admins_to_send_mail', $selected_admins)
      ->set('amount_of_products', $form_state->getValue('amount_of_products'))
      ->save();

    // Call the parent submit handler.
    parent::submitForm($form, $form_state);
  }

}
