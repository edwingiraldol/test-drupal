# Test Drupal

This is the 'Test Drupal' project, built on Drupal 11 using the Lando development environment.

## Prerequisites

- [Docker](https://www.docker.com/)
- [Lando](https://lando.dev/)

## Setup

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/edwingiraldol/test-drupal.git
   cd test-drupal
   lando start
2. **Install Dependencies:**
    ```bash
   lando composer install
3. **Create settings.local.php:**

   Inside the web/sites/default directory, create a file named settings.local.php and add the following code:

    ```php
    <?php
    $databases['default']['default'] = array (
    'database' => 'drupal11',
    'username' => 'drupal11',
    'password' => 'drupal11',
    'host' => 'database',
    'port' => 3306,
    'driver' => 'mysql',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
    );
    $settings['file_private_path'] = $app_root . '/sites/default/files/private';
    $settings['config_sync_directory'] = '../config/sync';
    ```

4. **Import the Database:**

   ```bash
   lando db-import dump.sql
   lando drush cr
   ```


