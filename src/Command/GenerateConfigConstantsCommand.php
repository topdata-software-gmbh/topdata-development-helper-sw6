<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use DOMDocument;
use DOMXPath;
use Exception;
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
    description: 'Parses a Shopware config.xml and generates a PHP constants class.',
)]
class GenerateConfigConstantsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('inputFile', InputArgument::REQUIRED, 'The path to the Shopware config.xml file.')
            ->addArgument('outputFile', InputArgument::OPTIONAL, 'The path for the generated PHP constants file. Defaults to standard output.')
            ->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Optional prefix for the constant value (e.g., "MyPlugin.system.config.").')
            ->addOption('className', null, InputOption::VALUE_REQUIRED, 'The class name for the generated file. Defaults to "PluginConstants".')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'The namespace for the generated file.', 'App\\Config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputFile = $input->getArgument('inputFile');
        $outputFile = $input->getArgument('outputFile');
        $prefix = $input->getOption('prefix') ?? '';
        $namespace = $input->getOption('namespace');
        // Provide a default class name if outputFile is null
        $className = $input->getOption('className') ?? ($outputFile ? pathinfo($outputFile, PATHINFO_FILENAME) : 'PluginConstants');


        // --- 1. Validate Input File ---
        if (!file_exists($inputFile) || !is_readable($inputFile)) {
            $io->error(sprintf('The input file does not exist or is not readable: "%s"', $inputFile));
            return Command::FAILURE;
        }

        // Validate output directory only if a file is specified
        if ($outputFile) {
            $outputDir = dirname($outputFile);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            if (!is_writable($outputDir)) {
                $io->error(sprintf('The output directory is not writable: "%s"', $outputDir));
                return Command::FAILURE;
            }
        }

        $io->title('Generating Config Constants');
        $io->horizontalTable(
            ['Source XML', 'Output', 'Prefix', 'Namespace', 'Class Name'],
            [[$inputFile, $outputFile ?: 'stdout', $prefix ?: '(none)', $namespace, $className]]
        );

        // --- 2. Parse XML and Extract Full Field Data ---
        try {
            $dom = new DOMDocument();
            @$dom->load($inputFile);
            $xpath = new DOMXPath($dom);
            // Query for all <input-field> elements
            $inputFieldNodes = $xpath->query('//input-field');

            if ($inputFieldNodes->length === 0) {
                $io->warning('No <input-field> elements found in the XML file.');
                return Command::SUCCESS;
            }

            $configData = [];
            foreach ($inputFieldNodes as $fieldNode) {
                $nameNode = $xpath->query('name', $fieldNode)->item(0);
                if (!$nameNode || empty(trim($nameNode->nodeValue))) {
                    continue; // Skip fields that have no name
                }
                $key = trim($nameNode->nodeValue);

                // Find label without a 'lang' attribute (defaults to English), fallback to first available
                $labelNode = $xpath->query('label[not(@lang)]', $fieldNode)->item(0) ?? $xpath->query('label', $fieldNode)->item(0);
                $label = $labelNode ? $this->cleanTextForComment($labelNode->nodeValue) : 'No label provided.';

                // Find helpText without a 'lang' attribute, fallback to first available
                $helpTextNode = $xpath->query('helpText[not(@lang)]', $fieldNode)->item(0) ?? $xpath->query('helpText', $fieldNode)->item(0);
                $helpText = $helpTextNode ? $this->cleanTextForComment($helpTextNode->nodeValue) : '';

                // Extract options
                $options = [];
                $optionsNode = $xpath->query('options', $fieldNode)->item(0);
                if ($optionsNode) {
                    $optionNodes = $xpath->query('option', $optionsNode);
                    foreach($optionNodes as $optionNode) {
                        $idNode = $xpath->query('id', $optionNode)->item(0);
                        $nameNode = $xpath->query('name[not(@lang)]', $optionNode)->item(0) ?? $xpath->query('name', $optionNode)->item(0);
                        if ($idNode && $nameNode) {
                            $options[] = [
                                'id' => trim($idNode->nodeValue),
                                'name' => $this->cleanTextForComment($nameNode->nodeValue),
                            ];
                        }
                    }
                }

                // Using key as index handles duplicates; last one wins.
                $configData[$key] = [
                    'label' => $label,
                    'helpText' => $helpText,
                    'options' => $options,
                ];
            }

            // Sort the data alphabetically by key for consistent output
            ksort($configData);

        } catch (Exception $e) {
            $io->error('Failed to parse the XML file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d unique configuration keys.', count($configData)));

        // --- 3. Generate PHP File Content ---
        $phpContent = $this->buildPhpFileContent($configData, $namespace, $className, $prefix);

        // --- 4. Write to File or STDOUT ---
        if ($outputFile) {
            try {
                file_put_contents($outputFile, $phpContent);
                $io->success(sprintf('Successfully generated constants file at: %s', $outputFile));
            } catch (Exception $e) {
                $io->error('Failed to write the output file: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            // Write directly to standard output
            $output->write($phpContent);
        }

        return Command::SUCCESS;
    }

    private function camelCaseToSnakeCase(string $input): string
    {
        $snake = preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", $input);
        return strtoupper($snake);
    }

    private function cleanTextForComment(string $text): string
    {
        // Replace newlines and multiple spaces with a single space and trim.
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function buildPhpFileContent(array $configData, string $namespace, string $className, string $prefix): string
    {
        $lines = [];
        $lines[] = '<?php declare(strict_types=1);';
        $lines[] = '';
        $lines[] = "namespace {$namespace};";
        $lines[] = '';
        $lines[] = '/**';
        $lines[] = ' * Contains constants for the plugin configuration keys.';
        $lines[] = ' *';
        $lines[] = ' * ! THIS FILE IS AUTO-GENERATED !';
        $lines[] = ' * ! Do not edit this file directly !';
        $lines[] = ' *';
        $lines[] = ' * Generated by: ' . self::class;
        $lines[] = ' * Generated at: ' . date('Y-m-d H:i:s');
        $lines[] = ' */';
        $lines[] = "final class {$className}";
        $lines[] = '{';

        $isFirstConstant = true;
        foreach ($configData as $key => $data) {
            if (!$isFirstConstant) {
                $lines[] = ''; // Add a blank line between constants for readability
            }
            $isFirstConstant = false;

            $baseConstantName = $this->camelCaseToSnakeCase($key);
            $constantValue = $prefix . $key;

            $lines[] = '    /**';
            $lines[] = '     * ' . wordwrap('Label: ' . $data['label'], 90, "\n     * ");
            if (!empty($data['helpText'])) {
                $lines[] = '     *';
                $lines[] = '     * ' . wordwrap('Help: ' . $data['helpText'], 90, "\n     * ");
            }

            if (!empty($data['options'])) {
                $lines[] = '     *';
                $lines[] = '     * @options Available choices:';
                foreach ($data['options'] as $option) {
                    $optionValueForComment = is_numeric($option['id'])
                        ? $option['id']
                        : "'" . addslashes($option['id']) . "'";
                    $lines[] = "     * - {$optionValueForComment}: {$option['name']}";
                }
            }

            $lines[] = '     */';
            $lines[] = sprintf("    public const %s = '%s';", $baseConstantName, $constantValue);
        }

        $lines[] = '}';
        $lines[] = ''; // Final newline

        return implode("\n", $lines);
    }
}