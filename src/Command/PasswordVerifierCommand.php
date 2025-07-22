<?php

namespace App\Command;

use parallel\Runtime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Service\PasswordService;
use App\UseCase\VerifyPassword\VerifyPassword;
use App\UseCase\VerifyPassword\VerifyPasswordRequest;

class PasswordVerifierCommand extends Command
{
    public function __construct()
    {
        parent::__construct("password:verify");
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Verifies the strength of a password')
            ->setHelp('Verify password strength (1-5)')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password to verify')
            ->addArgument('strength', InputArgument::OPTIONAL, 'Strength between 1 and 5')
            ->addOption('secret', 's', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $password = $input->getArgument('password');
        $strength = $input->getArgument('strength') ?? 3;
        $secret = $input->getOption('secret');

        if (!$password) {
            $question = new Question("What is the password you want to verify ?\n");
            if ($secret) {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $password = $helper->ask($input, $output, $question);
        }

        $progressIndicator = new ProgressIndicator($output, 'verbose', 100, ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇']);
        $progressIndicator->start('Processing...');

        $runtime = new Runtime();
        $future = $runtime->run(function () use ($password, $strength) {
            require __DIR__ . '/../../vendor/autoload.php'; // important !

            $pass = new VerifyPassword(
                new VerifyPasswordRequest($password, $strength),
                new PasswordService()
            );
            return $pass->execute();
        });

        while (!$future->done()) {
            $progressIndicator->advance();
            usleep(100_000); // 100 ms
        }

        $progressIndicator->finish('Finished');

        $resp = $future->value(); // résultat final

        if ($resp->exception) {
            $io->error("Une erreur est survenue : " . $resp->exception->getMessage());
            return Command::FAILURE;
        }

        $io->info("Password estimated strength : " . $resp->strength . " : " . PasswordService::getStrengthName($resp->strength));

        if ($resp->isLeaked) {
            $io->caution("Password is vulnerable, it has been leaked in a data breach.");
        }

        if ($resp->isOk) {
            $io->success("Password validates the expected strength.");
        } else {
            $io->caution("Password does not validate the expected strength.");
        }

        return Command::SUCCESS;
    }
}
