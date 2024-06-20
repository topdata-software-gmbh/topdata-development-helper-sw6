<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Doctrine\DBAL\Connection;
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
    private EntityRepository $propertyGroupRepository;
    private EntityRepository $productRepository;

    public function __construct(
        Connection $connection,
        EntityRepository $propertyGroupRepository,
        EntityRepository $productRepository
    ) {
        parent::__construct();
        $this->connection = $connection;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->productRepository = $productRepository;
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
        // ---- find them
        $unusedProperties = $this->getUnusedProperties();
        if (empty($unusedProperties)) {
            $this->cliStyle->success('No unused properties found.');
            return Command::SUCCESS;
        }


        // ---- delete them
        $unusedPropertiesString = implode(',', array_map(fn($x) => '0x' . bin2hex($x) , $unusedProperties));
        $sql = "DELETE FROM property_group_option WHERE id IN ($unusedPropertiesString)";
        $this->connection->executeStatement($sql);


        // ---- done
        $this->cliStyle->success(sprintf('Deleted %d unused properties.', count($unusedProperties)));

        return Command::SUCCESS;
    }










    private function getUnusedProperties(): array
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
    
    
    
}
