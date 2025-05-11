<?php

namespace App\Command;

use App\Service\PasswordService;
use App\UseCase\VerifyPassword\VerifyPassword;
use App\UseCase\VerifyPassword\VerifyPasswordRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasswordVerifierCommand extends Command
{

    public function __construct()
    {
        parent::__construct("password:verify");
    }

    protected function configure(): void
    {
        $this->addArgument('password', InputArgument::OPTIONAL, 'The password you want to verify.')
            ->addArgument('strength', InputArgument::OPTIONAL, 'The strength the password should validate. Number between 1 and 5 required.')
            ->addOption('secret',
                's',
                InputOption::VALUE_NONE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $password = $input->getArgument('password');
        $strength = $input->getArgument('strength');
        $secret = $input->getOption('secret');
        if (!$password) {
            $question = new Question("What is the password you want to verify ?\n");
            if ($secret) {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $password = $helper->ask($input, $output, $question);
        }
        $pass = new VerifyPassword(
            new VerifyPasswordRequest($password,$strength),
            new PasswordService()
        );
        $resp = $pass->execute();
        if ($resp->exception){
            $io->error("Une erreur est survenue : ".$resp->exception->getMessage());
            return Command::FAILURE;
        }

        $io->info("Password estimated strength :".$resp->strength);

        if($resp->isOk){
            $io->success("Password validates the expected strength.");
        } else {
            $io->caution("Password does not validate the expected strength.");
        }


        return Command::SUCCESS;
    }


}
