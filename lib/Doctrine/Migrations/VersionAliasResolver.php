<?php

declare(strict_types=1);

namespace Doctrine\Migrations;

use function substr;

/**
 * @internal
 */
final class VersionAliasResolver
{
    private const ALIAS_FIRST   = 'first';
    private const ALIAS_CURRENT = 'current';
    private const ALIAS_PREV    = 'prev';
    private const ALIAS_NEXT    = 'next';
    private const ALIAS_LATEST  = 'latest';

    /** @var MigrationRepository */
    private $migrationRepository;

    public function __construct(MigrationRepository $migrationRepository)
    {
        $this->migrationRepository = $migrationRepository;
    }

    /**
     * Returns the version number from an alias.
     *
     * Supported aliases are:
     *
     * - first: The very first version before any migrations have been run.
     * - current: The current version.
     * - prev: The version prior to the current version.
     * - next: The version following the current version.
     * - latest: The latest available version.
     *
     * If an existing version number is specified, it is returned verbatimly.
     */
    public function resolveVersionAlias(string $alias) : ?string
    {
        if ($this->migrationRepository->hasVersion($alias)) {
            return $alias;
        }

        switch ($alias) {
            case self::ALIAS_FIRST:
                return '0';

            case self::ALIAS_CURRENT:
                return $this->migrationRepository->getCurrentVersion();

            case self::ALIAS_PREV:
                return $this->migrationRepository->getPrevVersion();

            case self::ALIAS_NEXT:
                return $this->migrationRepository->getNextVersion();

            case self::ALIAS_LATEST:
                return $this->migrationRepository->getLatestVersion();

            default:
                if (substr($alias, 0, 7) === self::ALIAS_CURRENT) {
                    return $this->migrationRepository->getDeltaVersion(substr($alias, 7));
                }

                return null;
        }
    }
}
