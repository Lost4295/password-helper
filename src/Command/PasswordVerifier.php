<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasswordVerifier extends Command
{
    public const STRENGTH_VERY_WEAK = 0;
    public const STRENGTH_WEAK = 1;
    public const STRENGTH_MEDIUM = 2;
    public const STRENGTH_STRONG = 3;
    public const STRENGTH_VERY_STRONG = 4;

    public function __construct()
    {
        parent::__construct("password:verify");
    }

    protected function configure(): void
    {
        $this->addArgument('password', InputArgument::OPTIONAL, 'The password you want to verify.')
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
        $secret = $input->getOption('secret');
        if (!$password) {
            $question = new Question("What is the password you want to verify ?\n");
            if ($secret) {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $password = $helper->ask($input, $output, $question);
        }
        $io->info("Password : ". $password);
        return Command::SUCCESS;
    }

    public static function estimateStrength(#[\SensitiveParameter] string $password): int
    {
        if (!$length = \strlen($password)) {
            return self::STRENGTH_VERY_WEAK;
        }
        $password = count_chars($password, 1);
        $chars = \count($password);

        $control = $digit = $upper = $lower = $symbol = $other = 0;
        foreach ($password as $chr => $count) {
            match (true) {
                $chr < 32 || 127 === $chr => $control = 33,
                48 <= $chr && $chr <= 57 => $digit = 10,
                65 <= $chr && $chr <= 90 => $upper = 26,
                97 <= $chr && $chr <= 122 => $lower = 26,
                128 <= $chr => $other = 128,
                default => $symbol = 33,
            };
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;
        $entropy = $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);

        return match (true) {
            $entropy >= 120 => self::STRENGTH_VERY_STRONG,
            $entropy >= 100 => self::STRENGTH_STRONG,
            $entropy >= 80 => self::STRENGTH_MEDIUM,
            $entropy >= 60 => self::STRENGTH_WEAK,
            default => self::STRENGTH_VERY_WEAK,
        };
    }
}
