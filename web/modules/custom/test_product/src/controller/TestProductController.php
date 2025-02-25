<?php

namespace Drupal\test_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for handling product-related actions.
 */
class TestProductController extends ControllerBase
{

  /**
   * Records product interaction data and redirects to product page.
   *
   * Stores user interaction data when a product CTA is clicked, including:
   * - User ID
   * - Product ID
   * - IP address
   * - User type (anonymous/registered)
   * - Timestamp
   *
   * @param int $product_id
   *   The ID of the product node.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to the product node page.
   *
   * @throws \Exception
   *   If database insertion fails.
   */
  public function addDataProduct(int $product_id, Request $request): RedirectResponse
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $ip = $request->getClientIp();
    $user_type = $current_user->isAnonymous() ? 'anonymous' : 'registered';
    $created = \Drupal::time()->getRequestTime();

    try {
      \Drupal::database()->insert('test_register_product_cta')
        ->fields([
          'uid' => $uid,
          'product_id' => $product_id,
          'ip' => $ip,
          'user_type' => $user_type,
          'created' => $created,
        ])
        ->execute();
    } catch (\Exception $e){
      \Drupal::logger('test_product')->error($e->getMessage());
    }


    // Redirect to the product node page.
    $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $product_id])->toString();
    return new RedirectResponse($url);

  }

}
