<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ViktorRuskai\AdvancedUpsert\HasUpsert;

class UpsertQueryTest extends TestCase
{

    public function testCreateUpsertCommand()
    {
        $mock = $this->getMockForTrait(HasUpsert::class);

//        $mock
//            ->expects($this->any())
//            ->method('getConnectionResolver')
//            ->willReturn('dasd');
//        $mock
//            ->expects($this->once())
//            ->method('upsert')
//            ->willReturn('d');

        $mock::upsert([],[], []);
    }
}
