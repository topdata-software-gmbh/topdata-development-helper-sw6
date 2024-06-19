/**
 * 06/2023 created
 */
class TopdataDevelopmentHelperService {

    /**
     * disable the  annoying requests to the server every 5 seconds
     *
     * see https://stackoverflow.com/a/72914482/2848530
     */
    disableNotifications() {
        console.log("TopdataDevelopmentHelperService::disableNotifications()");
        Shopware.Service('notificationsService').fetchNotifications = () => Promise.resolve({ notifications: [] });
    }

}

export default new TopdataDevelopmentHelperService(); // create an instance