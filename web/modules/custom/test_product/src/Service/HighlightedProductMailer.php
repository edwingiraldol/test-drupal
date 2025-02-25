<?php
namespace Drupal\test_product\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Handles emailing of highlighted products to administrators.
 */
class HighlightedProductMailer {

  /**
   * @var \Drupal\Core\Database\Connection
   *   The database connection.
   */
  protected $database;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   *   The date formatter service.
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   *   The mail manager service.
   */
  protected $mailManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   *   The logger service.
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   *   The configuration for the test product settings.
   */
  protected $configFactory;

  /**
   * Constructs a new HighlightedProductMailer service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(Connection $database, DateFormatterInterface $dateFormatter, MailManagerInterface $mailManager, LoggerChannelFactory $logger, ConfigFactoryInterface $configFactory) {
    $this->database = $database;
    $this->dateFormatter = $dateFormatter;
    $this->mailManager = $mailManager;
    $this->logger = $logger->get('test_product');
    $this->configFactory = $configFactory->get('test_product.settings');
  }

  /**
   * Sends weekly product emails to admin users.
   *
   * @param array $adminEmails
   *   An array of admin email addresses.
   */
  public function sendWeeklyProductEmails(array $adminEmails) {
    // Calculate timestamp for one week ago.
    $one_week_ago = \Drupal::time()->getRequestTime() - (7 * 24 * 60 * 60);
    // Query for products highlighted as "Product of the Day".
    $query = $this->database->select('test_product_entries', 'tpe')
      ->fields('tpe', ['product_id'])
      ->condition('created', $one_week_ago, '>')
      ->groupBy('product_id')
      ->orderBy('COUNT(product_id)', 'DESC')
      ->range(0, $this->configFactory->get('amount_of_products'));

    $query->addExpression('COUNT(product_id)', 'product_count');
    $result = $query->execute();
    // Load product nodes and get their labels.
    $products = [];
    foreach ($result as $record) {
      $products[] = Node::load($record->product_id)->label();
    }

    // Compose email content.
    $message = 'Highlighted Products of the Week: ' . implode(', ', $products);

    // Send email to each admin.
    foreach ($adminEmails as $email) {
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $params['body'] = $message;
      $params['subject'] = 'Highlighted Products of the Week';
      $this->mailManager->mail('test_product', 'highlighted_products', $email, $langcode, $params, NULL, TRUE);
    }
  }

  /**
   * Retrieves all active admin users.
   *
   * @return \Drupal\user\Entity\User[]
   *   An array of User entities.
   */
  public function getAdminUsers(){
    // Query for active admin users.
    $query = \Drupal::entityQuery('user')
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->condition('roles', 'administrator'); // Adjust role as necessary
    $uids = $query->execute();

    return User::loadMultiple($uids);
  }

}

