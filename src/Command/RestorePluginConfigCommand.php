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
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Topdata\TopdataDevelopmentHelperSW6\Util\UtilAppPaths;
use Topdata\TopdataFoundationSW6\Command\AbstractTopdataCommand;

/**
 * 05/2024 created
 */
#[AsCommand(name: 'topdata:development-helper:plugin-config:restore')]
class RestorePluginConfigCommand extends AbstractTopdataCommand
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        parent::__construct();
        $this->systemConfigService = $systemConfigService;
    }

    protected function configure()
    {
        $this->setDescription('Restores the plugin configuration.');
        $this->addArgument('pluginName', InputArgument::REQUIRED, 'The technical name of the plugin, eg TopdataDemoshopSwitcherSW6');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cliStyle = new SymfonyStyle($input, $output);
        $pluginName = $input->getArgument('pluginName');

        // ---- Read the JSON configuration file
        $filesystem = new Filesystem();
        $dumpsDir = UtilAppPaths::getPluginConfigDumpDir();
        $configFile = $dumpsDir . '/' . $pluginName . '-config.json';

        if (!$filesystem->exists($configFile)) {
            $cliStyle->error('Configuration file not found for the plugin: ' . $pluginName);
            return Command::FAILURE;
        }

        $configJson = file_get_contents($configFile);
        $config = json_decode($configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $cliStyle->error('Invalid JSON configuration file for the plugin: ' . $pluginName);
            return Command::FAILURE;
        }

        // ---- Restore the configuration
        foreach ($config as $key => $value) {
            $cliStyle->writeln("Restoring: $key = $value");
            $this->systemConfigService->set($key, $value);
        }

        $cliStyle->success('Plugin configuration restored.');

        return Command::SUCCESS;
    }
}
