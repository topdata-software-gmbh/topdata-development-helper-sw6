<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Topdata\TopdataFoundationSW6\Command\AbstractTopdataCommand;

/**
 * 11/2024 created
 */
#[AsCommand(name: 'topdata:development-helper:delete-invalid-media')]
class DeleteInvalidMediaCommand extends AbstractTopdataCommand
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ParameterBagInterface $parameterBag,
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('it deletes rows from the `media` table where the physical file does not exist.');
    }


    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cliStyle->title('Deleting invalid media entries');

        // Get all media entries
        $mediaEntries = $this->connection->fetchAllAssociative(
            'SELECT id, path FROM media WHERE path IS NOT NULL'
        );

        $deletedCount = 0;
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        foreach ($mediaEntries as $entry) {
            $fullPath = $projectDir . '/public/' . $entry['path'];
            
            if (!file_exists($fullPath)) {
                // Delete the media entry
                $this->connection->executeStatement(
                    'DELETE FROM media WHERE id = :id',
                    ['id' => $entry['id']],
                    ['id' => \PDO::PARAM_STR]
                );
                $deletedCount++;
                
                $this->cliStyle->writeln(sprintf(
                    'Deleted media entry for missing file: %s',
                    $entry['path']
                ));
            }
        }

        $this->cliStyle->success(sprintf('Deleted %d invalid media entries', $deletedCount));
        return Command::SUCCESS;
    }
}
