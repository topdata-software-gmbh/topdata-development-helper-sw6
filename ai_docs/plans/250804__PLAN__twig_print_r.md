### Project: Add `print_r` Twig Function

**Goal:** Create a custom Twig function `{{ print_r(variable) }}` that provides a simple way to debug variables directly in template files, wrapping the output in `<pre><code>` tags for readability.

---

### Phase 1: Create the Twig Extension

The first step is to create the core logic for the new function. We will encapsulate this logic within a Twig Extension class.

1.  **Create a new directory:**
    Create a new directory to house the Twig extension file:
    ```bash
    mkdir -p src/Twig
    ```

2.  **Create the PHP class for the extension:**
    Create a new file named `DevelopmentHelperExtension.php` inside the `src/Twig/` directory. This class will define the `print_r` function.

    **File:** `src/Twig/DevelopmentHelperExtension.php`
    ```php
    <?php declare(strict_types=1);

    namespace Topdata\TopdataDevelopmentHelperSW6\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFunction;

    class DevelopmentHelperExtension extends AbstractExtension
    {
        public function getFunctions(): array
        {
            return [
                new TwigFunction('print_r', [$this, 'printR'], ['is_safe' => ['html']]),
            ];
        }

        /**
         * Takes any variable, gets the print_r output, and wraps it in <pre><code> tags.
         * The output is escaped to prevent security issues.
         *
         * @param mixed $variable The variable to debug.
         * @return string The HTML-formatted output.
         */
        public function printR($variable): string
        {
            $output = print_r($variable, true);
            $escapedOutput = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

            return '<pre><code>' . $escapedOutput . '</code></pre>';
        }
    }
    ```

### Phase 2: Register the Extension as a Service

Now that the class is created, we need to register it with Shopware's service container and tag it as a Twig extension so it's loaded automatically.

1.  **Modify the services.xml file:**
    Open `src/Resources/config/services.xml` and add a new service definition for the `DevelopmentHelperExtension`.

    **File:** `src/Resources/config/services.xml`
    ```xml
    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <services>

            <!-- TWIG EXTENSIONS -->
            <service id="Topdata\TopdataDevelopmentHelperSW6\Twig\DevelopmentHelperExtension">
                <tag name="twig.extension"/>
            </service>

            <!-- CONSOLE COMMANDS -->
            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\DeleteAllProductsCommand" autowire="true">
                <tag name="console.command"/>
            </service>

            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\DeleteUnusedPropertiesCommand" autowire="true">
                <tag name="console.command"/>
            </service>

            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\DumpPluginConfigCommand" autowire="true">
                <tag name="console.command"/>
            </service>

            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\RestorePluginConfigCommand" autowire="true">
                <tag name="console.command"/>
            </service>

            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\DeleteInvalidMediaCommand" autowire="true">
                <tag name="console.command"/>
            </service>

            <service id="Topdata\TopdataDevelopmentHelperSW6\Command\GenerateConfigConstantsCommand" autowire="true">
                <tag name="console.command"/>
            </service>


        </services>
    </container>
    ```

### Phase 3: Update Documentation

The final step is to document the new feature so that other developers know it exists and how to use it.

1.  **Edit the documentation file:**
    Open `docs/index.md` and add a new section for Twig functions.

    **File:** `docs/index.md`
    ```diff
    # config/packages/shopware.yaml
    shopware:
        admin_worker:
            enable_admin_worker: false
    ```
    
+   # Twig Functions
+   
+   ## print_r
+   This plugin provides a simple `print_r()` function for debugging variables directly within your Twig templates. The output is automatically wrapped in `<pre><code>` tags for readability.
+   
+   **Usage:**
+   ```twig
+   {# In any .html.twig file #}
+   
+   {{ print_r(page.header) }}
+   ```
+   
    # Console Commands
    
    ## topdata:development-helper:delete-all-products
    Deletes all products from the database.
    
    ...

