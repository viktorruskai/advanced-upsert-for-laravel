<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ItemAction;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * @method getObjectForTrait(string $class) @see \PHPUnit\Framework\TestCase
 */
class HasUpsertTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @param mixed $tested
     * @param mixed $expected
     *
     * @dataProvider valueDataProvider
     * @throws ReflectionException
     */
    public function testParseValues($tested, $expected): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'parseValues');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedParsedValue = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $tested,
        );
dump($returnedParsedValue);
        $this->assertSame($returnedParsedValue, $expected);
    }

    public function valueDataProvider(): array
    {
        return [
            [0, 0.0],
            [null, ''],
            ['test', '\'test\''],
            [new Expression('NOW()'), '\'NOW()\''],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testWrapValues(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $value = 'test123';

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'wrapValue');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedWrappedValue = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $value,
        );

        $this->assertSame('"' . $value . '"', $returnedWrappedValue);
    }

    /**
     * @throws ReflectionException
     */
    public function testCheckIfTimestampsAreAddedIntoItems(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $items = [
            'actionName' => 'Test',
            'actionDescription' => 'Test description',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'checkForTimestamps');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedItems = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            [$items],
        );

        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::UPDATED_AT]);
        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::CREATED_AT]);
        $this->assertSame('NOW()', $returnedItems[ItemAction::UPDATED_AT]->getValue());
        $this->assertSame('NOW()', $returnedItems[ItemAction::CREATED_AT]->getValue());
    }
}