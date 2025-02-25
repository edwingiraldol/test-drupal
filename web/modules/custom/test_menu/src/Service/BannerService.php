<?php

namespace Drupal\test_menu\Service;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Menu\MenuLinkManagerInterface;

/**
 * Service class to handle banner related operations.
 */
class BannerService {

  /**
   * The menu link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Constructs a BannerService object.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   The menu link manager service.
   */
  public function __construct(MenuLinkManagerInterface $menuLinkManager) {
    $this->menuLinkManager = $menuLinkManager;
  }

  /**
   * Retrieves the background field value for the active trail menu item.
   *
   * This method loads the main menu tree, identifies the active trail items,
   * and retrieves the background field value from the parent menu link entity.
   *
   * @return string|null
   *   The background field value or NULL if not found.
   * @throws PluginException
   */
  public function getBackgroundFieldValue() {
    // Load the menu tree for the current route with 'main' menu and set parameters.
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters('main');
    $parameters->setMinDepth(2);
    $parameters->onlyEnabledLinks();
    $main_menu_top_level = $menu_tree->load('main', $parameters);

    // Filter the menu items to find those in the active trail.
    $active_trail_items = array_filter($main_menu_top_level, function ($item) {
      return $item->inActiveTrail;
    });

    // If there are active trail items, retrieve the parent link and background field value.
    if ($active_trail_items) {
      // Create an instance of the parent link from
      $parent_link = $this->menuLinkManager->createInstance(reset($active_trail_items)->link->getParent());
      // Get the menu entity associated with the parent link.
      $menu_entity = $parent_link->getEntity();
      // Retrieve and return the background field URI value.
      return $menu_entity->get('field_background')->entity?->uri?->value ?? NULL;
    }

    return NULL;
  }

}
