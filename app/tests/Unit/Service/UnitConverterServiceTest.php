<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\UnitConverterService;
use PHPUnit\Framework\TestCase;

class UnitConverterServiceTest extends TestCase
{
    private UnitConverterService $service;

    protected function setUp(): void
    {
        $this->service = new UnitConverterService();
    }

    public function testConvertsGrams(): void
    {
        $result = $this->service->convert('100g');
        $this->assertSame(100.0, $result['quantity']);
        $this->assertSame('g', $result['unit']);
    }

    public function testConvertsKilograms(): void
    {
        $result = $this->service->convert('1.5kg');
        $this->assertSame(1500.0, $result['quantity']);
        $this->assertSame('g', $result['unit']);
    }

    public function testConvertsMillilitres(): void
    {
        $result = $this->service->convert('250ml');
        $this->assertSame(250.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsLitres(): void
    {
        $result = $this->service->convert('2l');
        $this->assertSame(2000.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsLitresUppercase(): void
    {
        $result = $this->service->convert('1L');
        $this->assertSame(1000.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsTeaspoon(): void
    {
        $result = $this->service->convert('2tsp');
        $this->assertSame(10.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsTablespoon(): void
    {
        $result = $this->service->convert('1tbsp');
        $this->assertSame(15.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsCup(): void
    {
        $result = $this->service->convert('1cup');
        $this->assertSame(250.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsCups(): void
    {
        $result = $this->service->convert('2cups');
        $this->assertSame(500.0, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsAsciiFraction(): void
    {
        $result = $this->service->convert('1/2 tsp');
        $this->assertSame(2.5, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsAsciiFractionWithWholePart(): void
    {
        $result = $this->service->convert('1 1/2 tbsp');
        $this->assertSame(22.5, $result['quantity']);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsUnicodeFractionHalf(): void
    {
        $result = $this->service->convert('½ tsp');
        $this->assertEqualsWithDelta(2.5, $result['quantity'], 0.01);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsUnicodeFractionTwoThirds(): void
    {
        $result = $this->service->convert('⅔ cup');
        $this->assertEqualsWithDelta(166.75, $result['quantity'], 0.5);
        $this->assertSame('ml', $result['unit']);
    }

    public function testConvertsUnicodeFractionThreeQuarters(): void
    {
        $result = $this->service->convert('¾ tsp');
        $this->assertEqualsWithDelta(3.75, $result['quantity'], 0.01);
        $this->assertSame('ml', $result['unit']);
    }

    public function testPlainNumberReturnsNullUnit(): void
    {
        $result = $this->service->convert('3');
        $this->assertSame(3.0, $result['quantity']);
        $this->assertNull($result['unit']);
    }

    public function testUnknownUnitReturnsNullQuantityAndUnit(): void
    {
        $result = $this->service->convert('2oz');
        $this->assertNull($result['quantity']);
        $this->assertNull($result['unit']);
    }

    public function testUnknownUnitPoundReturnsNull(): void
    {
        $result = $this->service->convert('1lb');
        $this->assertNull($result['quantity']);
        $this->assertNull($result['unit']);
    }

    public function testEmptyStringReturnsNull(): void
    {
        $result = $this->service->convert('');
        $this->assertNull($result['quantity']);
        $this->assertNull($result['unit']);
    }

    public function testConvertsWithSpaceBetweenNumberAndUnit(): void
    {
        $result = $this->service->convert('200 g');
        $this->assertSame(200.0, $result['quantity']);
        $this->assertSame('g', $result['unit']);
    }
}
