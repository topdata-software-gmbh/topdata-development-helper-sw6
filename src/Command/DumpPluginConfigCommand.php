<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Topdata\TopdataDevelopmentHelperSW6\Util\UtilAppPaths;
use Topdata\TopdataFoundationSW6\Command\AbstractTopdataCommand;

/**
 * 05/2024 created
 */
#[AsCommand(name: 'topdata:development-helper:plugin-config:dump')]
class DumpPluginConfigCommand extends AbstractTopdataCommand
{
    protected static $defaultName = 'topdata:development-helper:plugin-config:dump';

    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        parent::__construct();
        $this->systemConfigService = $systemConfigService;
    }


    protected function configure()
    {
        $this->setDescription('Dumps the plugin configuration.');
        $this->addArgument('pluginName', InputArgument::REQUIRED, 'The technical name of the plugin, eg TopdataDemoshopSwitcherSW6');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $cliStyle = new SymfonyStyle($input, $output);
        $pluginName = $input->getArgument('pluginName');

        // ---- Fetch the configuration for the given plugin and convert it to JSON
        $config = $this->systemConfigService->getDomain($pluginName . '.config.');
        if (empty($config)) {
            $cliStyle->warning('No configuration found for the plugin: ' . $pluginName);
            return Command::FAILURE;
        }
        $configJson = json_encode($config, JSON_PRETTY_PRINT);

        // ---- Create the plugin-configs directory if it doesn't exist
        $filesystem = new Filesystem();
        $dumpsDir = UtilAppPaths::getPluginConfigDumpDir();

        if (!$filesystem->exists($dumpsDir)) {
            $filesystem->mkdir($dumpsDir);
        }

        // ---- Save the JSON configuration to a file
        $configFile = $dumpsDir . '/' . $pluginName . '-config.json';
        $isNew = !$filesystem->exists($configFile);
        $filesystem->dumpFile($configFile, $configJson);

        // ---- done
        $cliStyle->success($configFile . ($isNew ? ' created.' : ' updated.'));

        return Command::SUCCESS;
    }
}
