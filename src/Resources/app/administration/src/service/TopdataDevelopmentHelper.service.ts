// src/Resources/app/administration/src/service/TopdataDevelopmentHelper.service.ts
// Use the global Shopware namespace available via types
declare const Shopware: typeof import('@shopware-ag/admin-extension-sdk/es/global-types') & {
    Service: (serviceName: string) => any;
    State: {
        // ... (add types for global state if needed)
    }
};

class TopdataDevelopmentHelperService {

    /**
     * Disables the annoying admin notification requests by overriding the fetchNotifications method.
     */
    disableNotifications(): void {
        console.log("TopdataDevelopmentHelperService::disableNotifications()");

        const notificationsService = Shopware.Service('notificationsService');

        if (notificationsService) {
            // Ensure we override the specific method safely
            notificationsService.fetchNotifications = () => {
                return Promise.resolve({ notifications: [] });
            };
        } else {
            console.error('notificationsService not found in Shopware.');
        }
    }
}

export default TopdataDevelopmentHelperService;