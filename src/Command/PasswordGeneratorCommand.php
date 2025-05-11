<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasswordGeneratorCommand extends Command

{

    public function __construct()
    {

        parent::__construct('password:generate');
    }
    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console list"
            ->setDescription('Generates a password with the provided paramaters')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a password. You can select th length and the strength of the password. ')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }
        $q= $io->askQuestion(new Question('tf are you man'));
        $section1 = $output->section();
        $section2 = $output->section();

        $section1->writeln('Hello');
        $section2->writeln($q);
        sleep(1);
        // Output displays "Hello\nWorld!\n"

        // overwrite() replaces all the existing section contents with the given content
        $section1->overwrite('Goodbye');
        sleep(1);
        // Output now displays "Goodbye\nWorld!\n"

        // clear() deletes all the section contents...
        $section2->clear();
        sleep(1);
        // Output now displays "Goodbye\n"

        // ...but you can also delete a given number of lines
        // (this example deletes the last two lines of the section)
        $section1->clear(2);
        sleep(1);
        // Output is now completely empty!

        // setting the max height of a section will make new lines replace the old ones
        $section1->setMaxHeight(2);
        $section1->writeln('Line1');
        $section1->writeln('Line2');
        $section1->writeln('Line3');
        $section2->writeln('Line4');
        return Command::SUCCESS;
    }

}
