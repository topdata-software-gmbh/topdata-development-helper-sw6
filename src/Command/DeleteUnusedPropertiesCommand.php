<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 06/2024 created
 */
#[AsCommand(name: 'topdata:development-helper:delete-unused-properties')]
class DeleteUnusedPropertiesCommand extends Command
{
    protected static $defaultName = 'topdata:development-helper:delete-unused-properties';

    private Connection $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }


    protected function configure()
    {
        $this->setDescription('it deletes unused properties from the database.');
    }


    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO...

        return Command::SUCCESS;
    }
}
