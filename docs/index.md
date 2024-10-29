
# Disabling Polling
- When this plugin is installed, it disables the polling of `/api/notification/message?limit=5`
- to disable the polling of `queue.json`, adjust the following configuration 
  (TODO: the plugin should provide a console command for this, also not `shopware.yaml`, but `z-shopware.yaml`): 
```yaml
# config/packages/shopware.yaml
shopware:
    admin_worker:
        enable_admin_worker: false
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

# TODO

see also: https://docs.shopware.com/en/shopware-6-en/tutorials-and-faq/sql-tips-tricks

