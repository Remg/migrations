<?php

declare(strict_types=1);

namespace Doctrine\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\MigratorConfig;

/**
 * @internal
 */
interface ExecutorInterface
{
    /**
     * @param mixed[] $params
     * @param mixed[] $types
     */
    public function addSql(string $sql, array $params = [], array $types = []) : void;

    public function execute(
        Version $version,
        AbstractMigration $migration,
        string $direction,
        ?MigratorConfig $migratorConfig = null
    ) : ExecutionResult;
}
