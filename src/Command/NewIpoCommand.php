<?php
namespace App\Command;

use App\Service\BrokerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewIpoCommand extends Command
{
    /** @var BrokerService */
    private $brokerService;
    protected static $defaultName = 'ipo:new';

    protected function configure()
    {
        $this
            ->addArgument('tjUserId')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

    public function __construct(BrokerService $brokerService)
    {
        $this->brokerService = $brokerService;
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $tjUserId = $input->getArgument('tjUserId');
        $ipo = $this->brokerService->IPO($tjUserId);
        $output->writeln('=== NEW IPO ===');
        echo $this->brokerService->printIPOInfo($ipo).PHP_EOL;
    }
}