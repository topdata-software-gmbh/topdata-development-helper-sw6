<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Command\AbstractTopdataCommand;

/**
 * 06/2023 created
 */
#[AsCommand(name: 'topdata:development-helper:delete-all-products')]
class DeleteAllProductsCommand extends AbstractTopdataCommand
{
    protected static $defaultName = 'topdata:development-helper:delete-all-products';

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
        $this->setDescription('it deletes all products from the database.');
    }


    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        dump($this->connection->getParams());

        if(!$this->cliStyle->confirm("really delete all products?")) {
            $this->cliStyle->warning("aborted");
            return Command::FAILURE;
        }

//        $sql = "
//            DELETE p.*, pt.* FROM product p
//            LEFT JOIN  product_translation pt ON p.id = pt.product_id
//        ";

        $sql = "DELETE FROM product";

        $ret = $this->connection->executeStatement($sql);
        dump($ret);


        return Command::SUCCESS;
    }
}
