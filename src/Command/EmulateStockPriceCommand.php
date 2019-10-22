<?php
namespace App\Command;

use App\Entity\StockPrice;
use App\Service\BrokerService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmulateStockPriceCommand extends Command
{
    /** @var BrokerService */
    private $brokerService;
    /** @var EntityManager */
    private $em;
    protected static $defaultName = 'emulate:stock:price';

    protected function configure()
    {
        $this
            ->addArgument('tjUserId')
            ->addOption('force')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

    public function __construct(BrokerService $brokerService, EntityManager $em)
    {
        $this->brokerService = $brokerService;
        $this->em = $em;
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $tjUserId = $input->getArgument('tjUserId');
        $force = $input->getOption('force');
        $ipo = $this->brokerService->IPO($tjUserId);
        $output->writeln('=== START EMULATE ===');
        $prices = $this->em->getRepository(StockPrice::class)
            ->createQueryBuilder('p')
            ->andWhere('p.publicOffering = :po')->setParameter('po', $ipo)
            ->addOrderBy('p.priceDate','ASC')
            ->getQuery()->getResult();
        /** @var StockPrice $priceItem */
        $prevPrice = null;
        foreach ($prices as $priceItem) {
            if($prevPrice == null) {
                $prevPrice = $priceItem;
                continue;
            }
            $newKarma = $priceItem->getCurrentKarma();
            $oldKarma = $priceItem->getOldKarma();
            $growth = $newKarma - $oldKarma;
            $aveGrowth = $this->brokerService->getAverageGrowth($ipo);
            if($aveGrowth == 0.0) {
                $aveGrowth = $growth;
            }
            $diff = $growth - $aveGrowth;
            $oldPrice = $prevPrice->getPrice();
            $price = $oldPrice;
            if($diff >= 5) {
                $price += floor($oldPrice*0.04 * 100) / 100;
            } elseif($diff >= 1) {
                $price += floor($oldPrice*0.02 * 100) / 100;
            } elseif($diff < 1 && $diff > -1) {

            } else {
                $price -= floor($oldPrice*0.01 * 100) / 100;
            }
            $priceItem->setPrice($price);
            $prevPrice = $priceItem;
            $date = $priceItem->getPriceDate()->format('d.m.Y H:i');
            $output->writeln("Date $date <> KARMA $oldKarma -> $newKarma <> Price $oldPrice -> $price <> Growth $aveGrowth $growth");
        }
        if($force) {
            $this->em->flush();
        }
    }
}