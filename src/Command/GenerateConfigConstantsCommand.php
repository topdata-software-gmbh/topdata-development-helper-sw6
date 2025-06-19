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
    description: 'Parses a Shopware config.xml and generates a PHP constants class with default values.',
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
        $className = $input->getOption('className') ?? ($outputFile ? pathinfo($outputFile, PATHINFO_FILENAME) : 'PluginConstants');


        // --- 1. Validate Input File ---
        if (!file_exists($inputFile) || !is_readable($inputFile)) {
            $io->error(sprintf('The input file does not exist or is not readable: "%s"', $inputFile));
            return Command::FAILURE;
        }

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
            $inputFieldNodes = $xpath->query('//input-field');

            if ($inputFieldNodes->length === 0) {
                $io->warning('No <input-field> elements found in the XML file.');
                return Command::SUCCESS;
            }

            $configData = [];
            foreach ($inputFieldNodes as $fieldNode) {
                $nameNode = $xpath->query('name', $fieldNode)->item(0);
                if (!$nameNode || empty(trim($nameNode->nodeValue))) {
                    continue;
                }
                $key = trim($nameNode->nodeValue);

                $labelNode = $xpath->query('label[not(@lang)]', $fieldNode)->item(0) ?? $xpath->query('label', $fieldNode)->item(0);
                $label = $labelNode ? $this->cleanTextForComment($labelNode->nodeValue) : 'No label provided.';

                $helpTextNode = $xpath->query('helpText[not(@lang)]', $fieldNode)->item(0) ?? $xpath->query('helpText', $fieldNode)->item(0);
                $helpText = $helpTextNode ? $this->cleanTextForComment($helpTextNode->nodeValue) : '';

                $defaultValueNode = $xpath->query('defaultValue', $fieldNode)->item(0);
                $defaultValue = $defaultValueNode ? trim($defaultValueNode->nodeValue) : null;

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

                $configData[$key] = [
                    'label' => $label,
                    'helpText' => $helpText,
                    'defaultValue' => $defaultValue,
                    'options' => $options,
                ];
            }

            ksort($configData);

        } catch (Exception $e) {
            $io->error('Failed to parse the XML file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d unique configuration keys.', count($configData)));
        $phpContent = $this->buildPhpFileContent($configData, $namespace, $className, $prefix);

        if ($outputFile) {
            try {
                file_put_contents($outputFile, $phpContent);
                $io->success(sprintf('Successfully generated constants file at: %s', $outputFile));
            } catch (Exception $e) {
                $io->error('Failed to write the output file: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
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
        $lines[] = ' * ! THIS FILE IS AUTO-GENERATED ! Do not edit this file directly !';
        $lines[] = ' * Generated by: ' . self::class;
        $lines[] = ' * Generated at: ' . date('Y-m-d H:i:s');
        $lines[] = ' */';
        $lines[] = "final class {$className}";
        $lines[] = '{';

        $isFirstConstant = true;
        foreach ($configData as $key => $data) {
            if (!$isFirstConstant) {
                $lines[] = '';
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

            // Add default value to the docblock, if it exists and is not an empty string
            if ($data['defaultValue'] !== null && $data['defaultValue'] !== '') {
                $lines[] = '     *';
                $defaultValueForComment = is_numeric($data['defaultValue'])
                    ? $data['defaultValue']
                    : "'" . addslashes($data['defaultValue']) . "'";
                $lines[] = "     * @default {$defaultValueForComment}";
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
        $lines[] = '';

        return implode("\n", $lines);
    }
}