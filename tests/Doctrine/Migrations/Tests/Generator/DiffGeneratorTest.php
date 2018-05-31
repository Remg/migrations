<?php

declare(strict_types=1);

namespace Doctrine\Migrations\Tests\Generator;

use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\Generator\DiffGenerator;
use Doctrine\Migrations\Generator\Generator;
use Doctrine\Migrations\Generator\SqlGenerator;
use Doctrine\Migrations\Provider\SchemaProviderInterface;
use PHPUnit\Framework\TestCase;

class DiffGeneratorTest extends TestCase
{
    /** @var DBALConfiguration */
    private $dbalConfiguration;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    /** @var SchemaProviderInterface */
    private $schemaProvider;

    /** @var AbstractPlatform */
    private $platform;

    /** @var Generator */
    private $migrationGenerator;

    /** @var SqlGenerator */
    private $migrationSqlGenerator;

    /** @var DiffGenerator */
    private $migrationDiffGenerator;

    public function testGenerate() : void
    {
        $fromSchema = $this->createMock(Schema::class);
        $toSchema   = $this->createMock(Schema::class);

        $this->dbalConfiguration->expects($this->once())
            ->method('setFilterSchemaAssetsExpression')
            ->with('/table_name1/');

        $this->dbalConfiguration->expects($this->once())
            ->method('getFilterSchemaAssetsExpression')
            ->willReturn('/table_name1/');

        $table1 = $this->createMock(Table::class);
        $table1->expects($this->once())
            ->method('getName')
            ->willReturn('schema.table_name1');

        $table2 = $this->createMock(Table::class);
        $table2->expects($this->once())
            ->method('getName')
            ->willReturn('schema.table_name2');

        $table3 = $this->createMock(Table::class);
        $table3->expects($this->once())
            ->method('getName')
            ->willReturn('schema.table_name3');

        $toSchema->expects($this->once())
            ->method('getTables')
            ->willReturn([$table1, $table2, $table3]);

        $this->schemaManager->expects($this->once())
            ->method('createSchema')
            ->willReturn($fromSchema);

        $this->schemaProvider->expects($this->once())
            ->method('createSchema')
            ->willReturn($toSchema);

        $toSchema->expects($this->at(1))
            ->method('dropTable')
            ->with('schema.table_name2');

        $toSchema->expects($this->at(2))
            ->method('dropTable')
            ->with('schema.table_name3');

        $fromSchema->expects($this->once())
            ->method('getMigrateToSql')
            ->with($toSchema, $this->platform)
            ->willReturn(['UPDATE table SET value = 2']);

        $fromSchema->expects($this->once())
            ->method('getMigrateFromSql')
            ->with($toSchema, $this->platform)
            ->willReturn(['UPDATE table SET value = 1']);

        $this->migrationSqlGenerator->expects($this->at(0))
            ->method('generate')
            ->with(['UPDATE table SET value = 2'], true, 80)
            ->willReturn('test1');

        $this->migrationSqlGenerator->expects($this->at(1))
            ->method('generate')
            ->with(['UPDATE table SET value = 1'], true, 80)
            ->willReturn('test2');

        $this->migrationGenerator->expects($this->once())
            ->method('generateMigration')
            ->with('1234', 'test1', 'test2')
            ->willReturn('path');

        self::assertEquals('path', $this->migrationDiffGenerator->generate('1234', '/table_name1/', true, 80));
    }

    protected function setUp() : void
    {
        $this->dbalConfiguration      = $this->createMock(DBALConfiguration::class);
        $this->schemaManager          = $this->createMock(AbstractSchemaManager::class);
        $this->schemaProvider         = $this->createMock(SchemaProviderInterface::class);
        $this->platform               = $this->createMock(AbstractPlatform::class);
        $this->migrationGenerator     = $this->createMock(Generator::class);
        $this->migrationSqlGenerator  = $this->createMock(SqlGenerator::class);
        $this->migrationDiffGenerator = $this->createMock(DiffGenerator::class);

        $this->migrationDiffGenerator = new DiffGenerator(
            $this->dbalConfiguration,
            $this->schemaManager,
            $this->schemaProvider,
            $this->platform,
            $this->migrationGenerator,
            $this->migrationSqlGenerator,
            $this->migrationDiffGenerator
        );
    }
}
