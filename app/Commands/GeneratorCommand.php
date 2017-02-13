<?php
namespace App\Commands;

use App\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorCommand extends Command
{
    protected function configure()
    {
        $this->setName('generator')
            ->setDescription('Generates a new Laravel application.')
            ->setHelp("This command allows you to create users...");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Schema Creator',
            '==============',
            '',
        ]);

        $data = json_decode(file_get_contents("schema.json", "r"));

        foreach ($data->models as $details) {
            $model = new Model($details);
            $model->generate();
        }
    }

    
}