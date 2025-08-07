# Topdata Development Helper SW6

A Shopware 6 development helper plugin that provides various utilities for development environments including database cleanup commands, configuration management tools, and debugging helpers.

## Features

- Delete all products command - removes all products from the database
- Delete invalid media entries - removes media database entries for missing physical files
- Delete unused properties - removes unused property group options and empty property groups
- Dump plugin configuration - exports plugin configuration to JSON files
- Restore plugin configuration - imports plugin configuration from JSON files
- Generate config constants - creates PHP constants classes from config.xml files
- Twig print_r function - adds {% raw %}{{ print_r(variable) }}{% endraw %} for debugging in templates
- Admin notification disabler - disables annoying admin notification requests

For detailed documentation, see the [manual directory](manual/).
