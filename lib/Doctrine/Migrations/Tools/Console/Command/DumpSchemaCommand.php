<?php

declare(strict_types=1);

namespace Doctrine\Migrations\Tools\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function count;
use function sprintf;

class DumpSchemaCommand extends AbstractCommand
{
    protected function configure() : void
    {
        parent::configure();

        $this
            ->setName('migrations:dump-schema')
            ->setAliases(['dump-schema'])
            ->setDescription('Dump the schema for your database to a migration.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command dumps the schema for your database to a migration:

    <info>%command.full_name%</info>

After dumping your schema to a migration, you can rollup your migrations using the <info>migrations:rollup</info> command.
EOT
            )
            ->addOption(
                'editor-cmd',
                null,
                InputOption::VALUE_OPTIONAL,
                'Open file with this command upon creation.'
            )
            ->addOption(
                'formatted',
                null,
                InputOption::VALUE_NONE,
                'Format the generated SQL.'
            )
            ->addOption(
                'line-length',
                null,
                InputOption::VALUE_OPTIONAL,
                'Max line length of unformatted lines.',
                120
            )
        ;
    }

    public function execute(
        InputInterface $input,
        OutputInterface $output
    ) : void {
        $formatted  = (bool) $input->getOption('formatted');
        $lineLength = (int) $input->getOption('line-length');

        $schemaDumper = $this->dependencyFactory->getSchemaDumper();
        $versions     = $this->migrationRepository->getVersions();

        if (count($versions) > 0) {
            throw new RuntimeException('Delete your old historical migrations before dumping your schema.');
        }

        $versionNumber = $this->configuration->generateVersionNumber();

        $path = $schemaDumper->dump(
            $versionNumber,
            $formatted,
            $lineLength
        );

        $editorCommand = $input->getOption('editor-cmd');

        if ($editorCommand !== null) {
            $this->procOpen($editorCommand, $path);
        }

        $output->writeln([
            sprintf('Dumped your schema to a new migration class at "<info>%s</info>"', $path),
            '',
            sprintf(
                'To run just this migration for testing purposes, you can use <info>migrations:execute --up %s</info>',
                $versionNumber
            ),
            '',
            sprintf(
                'To revert the migration you can use <info>migrations:execute --down %s</info>',
                $versionNumber
            ),
            '',
            'To use this as a rollup migration you can use the <info>migrations:rollup</info> command.',
        ]);
    }
}
