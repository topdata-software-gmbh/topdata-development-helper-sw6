import TopdataDevelopmentHelperService from './service/TopdataDevelopmentHelper.service';

// Register the service globally
Shopware.Service('TopdataDevelopmentHelperService', new TopdataDevelopmentHelperService());

// Execute the logic when the plugin is initialized (optional but recommended)
// We call it here directly for simplicity, as it's a one-time action.
Shopware.Service('TopdataDevelopmentHelperService').disableNotifications();