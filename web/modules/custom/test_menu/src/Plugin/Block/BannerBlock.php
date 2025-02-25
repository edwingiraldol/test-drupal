<?php

declare(strict_types=1);

namespace Drupal\test_menu\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
 * Provides a 'Banner' Block.
 *
 * This block displays a banner that can be configured to show a title
 * and a background image. It uses a service to retrieve background data.
 */
#[Block(
  id: 'test_menu_banner',
  admin_label: new TranslatableMarkup('Banner'),
  category: new TranslatableMarkup('Custom'),
)]
final class BannerBlock extends BlockBase {

  /**
   * @var mixed
   * The banner service used to get background field values.
   */
  protected mixed $bannerService;

  /**
   * Constructs a BannerBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the block.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Initialize the banner service.
    $this->bannerService = \Drupal::service('test_menu.banner_service');
  }

  /**
   * {@inheritdoc}
   *
   * Defines the default configuration for the block.
   */
  public function defaultConfiguration(): array {
    return [
      'title' => '', // Default title is an empty string.
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Builds the form for configuring this block.
   *
   * @param array $form
   *   The form definition array for the block configuration.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form definition array.
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['title'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['title'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Handles the form submission for configuring this block.
   *
   * @param array $form
   *   The form definition array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    // Stores the submitted title in the block's configuration.
    $this->configuration['title'] = $form_state->getValue('title');
  }

  /**
   * {@inheritdoc}
   *
   * Builds the block content.
   *
   * @return array
   *   A render array representing the block content.
   */
  public function build(): array {
    $field_value = $this->bannerService->getBackgroundFieldValue();

    $cache = [
      'contexts' => ['url.path'],
      'tags' => ['config:system.menu.main'],
    ];
    return  $field_value == NULL ? [
      '#cache' => $cache,
      ] : [
      '#theme' => 'block__test_menu__banner',
      '#title' => $this->configuration['title'],
      '#background_image' => $field_value,
      '#cache' => $cache,
      '#attached' => [
        'library' => [
          'test_menu/test_menu',
        ],
      ],
    ];
  }
}
