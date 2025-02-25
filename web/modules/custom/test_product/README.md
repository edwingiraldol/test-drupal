# Test Product Module

The Test Product module provides enhanced functionality for managing and promoting products within your Drupal site.

## Features

- Custom product form alterations to ensure a maximum of 5 products can be set as "Product of the Day".
- Sends weekly emails highlighting products of the day to admin users.
- Provides a configuration form for setting email recipients and the number of products to highlight.
- Integrates with Views for product-related displays with custom theming capabilities.

## Requirements

- None

## Installation

1. Place the `test_product` module in the `modules/custom` directory of your Drupal installation.
2. Enable the module via the Drupal admin interface or with Drush:
   ```bash
   drush en test_product
