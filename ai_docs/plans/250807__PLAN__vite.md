This guide provides detailed instructions on migrating the administration JavaScript of the `TopdataDevelopmentHelperSW6` plugin from its current structure to a Shopware 6.7 compatible Vite and TypeScript environment.

The current administration code is located in `src/Resources/app/administration/src/` and is initialized in `src/Resources/app/administration/src/main.js`. We will convert this to TypeScript and configure it for Shopware 6.7's Vite build system.

### Prerequisites

1.  **Shopware 6.7+ Setup:** Ensure your Shopware environment is running version 6.7 or newer.
2.  **Administration Tooling:** Ensure you have the necessary Node.js dependencies installed in the `platform/src/Administration/Resources/app/administration` directory.

### Phase 1: Setting up the Vite and TypeScript Environment

1.  **Install Dependencies for the Plugin:**
    Navigate to the plugin's administration directory:
    ```bash
    cd src/Resources/app/administration
    ```

    Create a `package.json` file in this directory if it does not already exist:
    ```json
    {
      "name": "topdata-development-helper-sw6-administration",
      "version": "1.0.0",
      "private": true,
      "type": "module",
      "scripts": {
        "build": "vite build"
      },
      "devDependencies": {
        "@shopware-ag/vite-plugin-shopware": "^1.0.0",
        "vite": "^5.0.0"
      }
    }
    ```
    Then, install the dependencies:
    ```bash
    npm install
    ```

2.  **Create Vite Configuration:**
    Create a `vite.config.js` file in `src/Resources/app/administration`:

    ```javascript
    import { defineConfig } from 'vite';
    import shopware from '@shopware-ag/vite-plugin-shopware';

    export default defineConfig({
      plugins: [
        shopware({
          sourcePath: __dirname,
          admin: {
            entry: './src/main.ts',
          },
        }),
      ],
      // Ensure Vite outputs to the correct directory relative to the plugin
      build: {
        outDir: './dist/administration',
      },
      resolve: {
        extensions: ['.ts', '.js'],
      },
    });
    ```

3.  **Setup TypeScript Configuration:**
    Create a `tsconfig.json` file in `src/Resources/app/administration`. You can use a basic configuration suitable for Shopware 6.7:

    ```json
    {
      "extends": "@tsconfig/vite/tsconfig.json",
      "compilerOptions": {
        "baseUrl": ".",
        "target": "ESNext",
        "module": "ESNext",
        "moduleResolution": "Node",
        "strict": true,
        "jsx": "preserve",
        "sourceMap": true,
        "resolveJsonModule": true,
        "esModuleInterop": true,
        "skipLibCheck": true,
        "lib": ["ESNext", "DOM"],
        "types": ["node", "shopware-extensions", "shopware-platform"]
      },
      "include": ["src/**/*.ts", "src/**/*.d.ts", "src/**/*.js", "src/**/*.vue"],
      "exclude": ["node_modules"]
    }
    ```
    *Note: If `@tsconfig/vite/tsconfig.json` is not available, you might need to install it: `npm install @tsconfig/vite`.*

### Phase 2: Refactoring the JavaScript Code to TypeScript

1.  **Refactor `main.js` to `main.ts`:**
    Rename `src/Resources/app/administration/src/main.js` to `src/Resources/app/administration/src/main.ts`.

    Update the content of `main.ts` to use a consistent import and registration pattern (although the existing code just executes immediately, this ensures it's compatible with the new build system):

    ```typescript
    import TopdataDevelopmentHelperService from './service/TopdataDevelopmentHelper.service';

    // Register the service globally
    Shopware.Service('TopdataDevelopmentHelperService', new TopdataDevelopmentHelperService());

    // Execute the logic when the plugin is initialized (optional but recommended)
    // We call it here directly for simplicity, as it's a one-time action.
    Shopware.Service('TopdataDevelopmentHelperService').disableNotifications();
    ```

2.  **Refactor `TopdataDevelopmentHelper.service.js` to `TopdataDevelopmentHelper.service.ts`:**
    Rename `src/Resources/app/administration/src/service/TopdataDevelopmentHelper.service.js` to `src/Resources/app/administration/src/service/TopdataDevelopmentHelper.service.ts`.

    Update the content, adding TypeScript typing and ensuring it's properly exported as a class:

    ```typescript
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
    ```

### Phase 3: Building the Administration Assets

1.  **Clean up old build files:**
    Delete the old compiled JavaScript files to avoid conflicts:
    ```bash
    rm -f src/Resources/public/administration/js/topdata-development-helper-s-w6.js
    ```

2.  **Run the Build Command:**
    Shopware 6.7 provides a unified command to build plugin administration assets. Navigate to the root of your Shopware installation and run:

    ```bash
    bin/console bundle:build
    # or specifically for administration plugins if available in your environment:
    # bin/console administration:build-plugins
    ```

3.  **Verify the Output:**
    The new build system will generate the compiled files in the `src/Resources/app/administration/dist/administration` directory (as configured in `vite.config.js`).

### Summary of Changes

*   **`composer.json`**: No changes needed here, but ensures compatibility range is correct.
*   **`src/Resources/app/administration/`**: New `package.json`, `vite.config.js`, and `tsconfig.json` added.
*   **`src/Resources/app/administration/src/`**: `main.js` and `TopdataDevelopmentHelper.service.js` were renamed to `.ts` and updated to use TypeScript syntax.
*   **`src/Resources/public/`**: This folder should now contain the new `dist/administration` output from Vite, which is handled automatically by the build command.
*   **`src/Resources/config/services.xml`**: Remains unchanged as it only manages PHP services and console commands.

This migration ensures the administration part of the plugin is built using the correct Vite tooling, enabling compatibility with Shopware 6.7 and future versions.

