<?php
namespace App\Command;

use App\Entity\PublicOffering;
use App\Service\BrokerService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStockPriceHourCommand extends Command
{
    /** @var BrokerService */
    private $brokerService;
    /** @var EntityManager */
    private $em;
    protected static $defaultName = 'stock:update-price:hour';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

    public function __construct(BrokerService $brokerService, EntityManagerInterface $em)
    {
        $this->brokerService = $brokerService;
        $this->em = $em;
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('=== NEW PRICE CALCULATION ===');
        $now = new \DateTime();
        $from = (clone $now)->setTime(9,0,0,0);
        $to = (clone $now)->setTime(22,0,0,0);
        if($now > $to || $now < $from) {
            $output->writeln('NOT WORKING, NOW '.$now->format('H:i'));
            return;
        }
        $ipos = $this->em->getRepository(PublicOffering::class)
            ->findAll();
        /** @var PublicOffering $ipo */
        foreach ($ipos as $ipo)
        {
            $this->brokerService->calculateNewPrice($ipo);
        }
        $output->writeln('=== NEW PRICE END ===');
    }
}