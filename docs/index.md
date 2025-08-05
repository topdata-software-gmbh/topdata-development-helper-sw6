
# Disabling Polling
- When this plugin is installed, it disables the polling of `/api/notification/message?limit=5` [needs to run `bin/build-administration.sh` once]
- to disable the polling of `queue.json`, adjust the following configuration 
  (TODO: the plugin should provide a console command for this, also not `shopware.yaml`, but `z-shopware.yaml`): 
```yaml
# config/packages/shopware.yaml
shopware:
    admin_worker:
        enable_admin_worker: false
```


# Twig Functions

## print_r
This plugin provides a simple `print_r()` function for debugging variables directly within your Twig templates. The output is automatically wrapped in `<pre><code>` tags for readability.

**Usage:**
```twig
{# In any .html.twig file #}

{{ print_r(page.header) }}
```

# Console Commands

## topdata:development-helper:delete-all-products
Deletes all products from the database.

## topdata:development-helper:plugin-config:dump
Dumps the plugin configuration to a JSON file in the `plugin-config-dumps` directory.

## topdata:development-helper:plugin-config:restore
Restores the plugin configuration from a JSON file in the `plugin-config-dumps` directory.

## topdata:development-helper:delete-unused-properties
It deletes unused properties group options and empty property groups from the database.

## topdata:development-helper:delete-invalid-media
Deletes media entries from the database where the corresponding physical files don't exist in the filesystem.

# TODO

see also: https://docs.shopware.com/en/shopware-6-en/tutorials-and-faq/sql-tips-tricks

