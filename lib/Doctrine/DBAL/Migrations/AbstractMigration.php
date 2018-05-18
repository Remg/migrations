<?php

namespace Doctrine\DBAL\Migrations;

use Doctrine\Migrations\AbstractMigration as BaseAbstractMigration;

@trigger_error(sprintf('The "%s" class is deprecated since Doctrine Migrations 2.0. Use %s instead.', AbstractMigration::class, BaseAbstractMigration::class), E_USER_DEPRECATED);

/**
 * @deprecated  Please use Doctrine\Migrations\AbstractMigration
 */
abstract class AbstractMigration extends BaseAbstractMigration
{
}
