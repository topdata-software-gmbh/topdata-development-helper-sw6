<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 06/2024 created
 */
#[AsCommand(name: 'topdata:development-helper:delete-unused-properties')]
class DeleteUnusedPropertiesCommand extends AbstractCommand
{
    protected static $defaultName = 'topdata:development-helper:delete-unused-properties';

    private Connection $connection;
    private string $defaultLanguageId; // hex sw6 id

    public function __construct(
        Connection $connection
    ) {
        parent::__construct();
        $this->connection = $connection;
    }
    

    protected function configure()
    {
        $this->setDescription('it deletes unused properties group options and empty property groups from the database.');
    }

    private function _deleteEmptyPropertyGroups(): void
    {
        // Find all property groups that do not have any associated options
        $sql = "SELECT pg.id, pgt.name FROM property_group pg 
            LEFT JOIN property_group_option pgo 
                ON pg.id = pgo.property_group_id 
            LEFT JOIN property_group_translation pgt 
                ON pg.id = pgt.property_group_id AND pgt.language_id = UNHEX(:defaultLanguageId)
            -- This part of the query is the condition that filters out the property groups that have associated options. If the id in the property_group_option table is NULL, it means that the property group does not have any associated options. 
           WHERE pgo.id IS NULL
           ";
        $emptyPropertyGroups = $this->connection->executeQuery($sql, [
            'defaultLanguageId' => $this->defaultLanguageId,
        ])->fetchAllAssociative();

        if (empty($emptyPropertyGroups)) {
            $this->cliStyle->success('No empty property groups found.');
            return;
        }

        // Print the names of the empty property groups and ask for confirmation
        foreach ($emptyPropertyGroups as $propertyGroup) {
            $this->cliStyle->writeln("Empty property group: " . $propertyGroup['name']);
        }

        if (!$this->cliStyle->confirm('Really delete these ' . count($emptyPropertyGroups) . ' empty property groups?')) {
            return;
        }

        // Delete the empty property groups
        $emptyPropertyGroupsString = implode(',', array_map(fn($x) => '0x' . bin2hex($x['id']) , $emptyPropertyGroups));
        $sql = "DELETE FROM property_group WHERE id IN ($emptyPropertyGroupsString)";
        $this->connection->executeStatement($sql);

        $this->cliStyle->success(sprintf('Deleted %d empty property groups.', count($emptyPropertyGroups)));
    }

    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ---- get default language id
        $this->defaultLanguageId = Defaults::LANGUAGE_SYSTEM;

        $this->_deleteUnusedPropertyGroupOptions();

        $this->_deleteEmptyPropertyGroups();


        return Command::SUCCESS;
    }










    private function getUnusedPropertyGroupOptionIds(): array
    {
        $usedPropertyIds = $this->getUsedPropertyIds();
        $this->cliStyle->green(count($usedPropertyIds) . ' used properties found.');

        $allPropertyIds = $this->getAllPropertyIds();
        $this->cliStyle->green(count($allPropertyIds) . ' all properties found.');

        $ret = array_diff($allPropertyIds, $usedPropertyIds);
        $this->cliStyle->green(count($ret) . ' unused properties found.');

        return $ret;
    }


    private function getUsedPropertyIds(): array
    {
        $sql = 'SELECT DISTINCT property_group_option_id FROM product_property';

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }



    private function getAllPropertyIds(): array
    {
        $sql = 'SELECT id FROM property_group_option';

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }

    private function _deleteUnusedPropertyGroupOptions(): void
    {
        // ---- find them
        $unusedPropertyGroupOptionIds = $this->getUnusedPropertyGroupOptionIds();
        if (empty($unusedPropertyGroupOptionIds)) {
            $this->cliStyle->success('No unused properties found.');
            return;
        }


        // ---- get names of unused properties before deleting them
        $unusedPropertiesString = implode(',', array_map(fn($x) => '0x' . bin2hex($x) , $unusedPropertyGroupOptionIds));
        $sql = "SELECT pgot.name FROM property_group_option pgo 
                LEFT JOIN property_group_option_translation pgot 
                    ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(:defaultLanguageId) 
                 WHERE pgo.id IN ($unusedPropertiesString)";
        $unusedPropertyNames = $this->connection->executeQuery($sql, ['defaultLanguageId' => $this->defaultLanguageId])->fetchFirstColumn();
        $this->cliStyle->writeln(implode("\n", $unusedPropertyNames));

        if($this->cliStyle->confirm("Really delete these ".count($unusedPropertyNames)." property group options?")) {
            // ---- delete the unused property group options
            $unusedPropertiesString = implode(',', array_map(fn($x) => '0x' . bin2hex($x) , $unusedPropertyGroupOptionIds));
            $sql = "DELETE FROM property_group_option WHERE id IN ($unusedPropertiesString)";
            $this->connection->executeStatement($sql);
        }

        // ---- done
        $this->cliStyle->success(sprintf('Deleted %d unused properties.', count($unusedPropertyGroupOptionIds)));


    }


}
