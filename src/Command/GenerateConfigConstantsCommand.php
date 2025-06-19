<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 06/2025 created
 */
#[AsCommand(
    name: 'topdata:development-helper:generate-config-constants',
    description: 'Parses a Shopware config.xml file and generates a PHP constants file.',
)]
class GenerateConfigConstantsCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('inputFile', InputArgument::REQUIRED, 'The path to the Shopware config.xml file.');
        $this->addArgument('outputFile', InputArgument::REQUIRED, 'The path for the generated PHP constants file (e.g., src/Config/PluginConstants.php).');
        $this->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Optional prefix for the constant value (e.g., "MyPlugin.system.config.").');
        $this->addOption('className', null, InputOption::VALUE_REQUIRED, 'The class name for the generated file. Defaults to the output filename.');
        $this->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'The namespace for the generated file.', 'App\\Config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputFile = $input->getArgument('inputFile');
        $outputFile = $input->getArgument('outputFile');
        $prefix = $input->getOption('prefix') ?? '';
        $namespace = $input->getOption('namespace');
        $className = $input->getOption('className') ?? pathinfo($outputFile, PATHINFO_FILENAME);

        // --- 1. Validate Inputs ---
        if (!file_exists($inputFile) || !is_readable($inputFile)) {
            $io->error(sprintf('The input file does not exist or is not readable: "%s"', $inputFile));
            return Command::FAILURE;
        }

        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        if (!is_writable($outputDir)) {
            $io->error(sprintf('The output directory is not writable: "%s"', $outputDir));
            return Command::FAILURE;
        }

        $io->title('Generating Config Constants');
        $io->horizontalTable(
            ['Source XML', 'Output PHP', 'Prefix', 'Namespace', 'Class Name'],
            [[$inputFile, $outputFile, $prefix ?: '(none)', $namespace, $className]]
        );


        // --- 2. Parse XML and Extract Keys ---
        try {
            $dom = new \DOMDocument();
            // Suppress warnings for things like the schema location not being found locally
            @$dom->load($inputFile);

            $xpath = new \DOMXPath($dom);
            // Query for all <name> elements that are direct children of <input-field>
            $nameNodes = $xpath->query('//input-field/name');

            if ($nameNodes->length === 0) {
                $io->warning('No <input-field>/<name> elements found in the XML file.');
                return Command::SUCCESS;
            }

            $configKeys = [];
            foreach ($nameNodes as $node) {
                // Trim the value to handle whitespace
                $key = trim($node->nodeValue);
                if (!empty($key)) {
                    $configKeys[] = $key;
                }
            }
            // Ensure we only have unique keys
            $uniqueKeys = array_unique($configKeys);
            sort($uniqueKeys);

        } catch (\Exception $e) {
            $io->error('Failed to parse the XML file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d unique configuration keys.', count($uniqueKeys)));


        // --- 3. Generate PHP File Content ---
        $phpContent = $this->buildPhpFileContent($uniqueKeys, $namespace, $className, $prefix);


        // --- 4. Write to File ---
        try {
            file_put_contents($outputFile, $phpContent);
        } catch (\Exception $e) {
            $io->error('Failed to write the output file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully generated constants file at: %s', $outputFile));

        return Command::SUCCESS;
    }

    /**
     * Converts a camelCase string to UPPERCASE_SNAKE_CASE.
     */
    private function camelCaseToSnakeCase(string $input): string
    {
        // Add an underscore before any uppercase letter that is preceded by a lowercase letter or a digit.
        $snake = preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", $input);
        return strtoupper($snake);
    }

    /**
     * Builds the full content of the PHP constants file.
     */
    private function buildPhpFileContent(array $keys, string $namespace, string $className, string $prefix): string
    {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = "namespace {$namespace};";
        $lines[] = '';
        $lines[] = '/**';
        $lines[] = ' * Contains constants for the plugin configuration keys.';
        $lines[] = ' *';
        $lines[] = ' * ! THIS FILE IS AUTO-GENERATED !';
        $lines[] = ' * ! Do not edit this file directly !';
        $lines[] = ' *';
        $lines[] = ' * Generated by: App\Command\GenerateConfigConstantsCommand';
        $lines[] = ' * Generated at: ' . date('Y-m-d H:i:s');
        $lines[] = ' */';
        $lines[] = "final class {$className}";
        $lines[] = '{';

        foreach ($keys as $key) {
            $constantName = $this->camelCaseToSnakeCase($key);
            $constantValue = $prefix . $key;

            $lines[] = sprintf("    public const %s = '%s';", $constantName, $constantValue);
        }

        $lines[] = '}';
        $lines[] = ''; // Final newline

        return implode("\n", $lines);
    }
}