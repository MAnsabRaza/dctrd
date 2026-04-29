<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UnitConversionService;

class UnitConversionServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnitConversionService();
    }

    /** @test */
    public function it_converts_kilometers_to_miles()
    {
        $result = $this->service->convertLength(10, 'km', 'mi');
        $this->assertEqualsWithDelta(6.21, $result, 0.01);
    }

    /** @test */
    public function it_converts_miles_to_kilometers()
    {
        $result = $this->service->convertLength(10, 'mi', 'km');
        $this->assertEqualsWithDelta(16.09, $result, 0.01);
    }

    /** @test */
    public function it_converts_kilograms_to_pounds()
    {
        $result = $this->service->convertMass(10, 'kg', 'lbs');
        $this->assertEqualsWithDelta(22.05, $result, 0.01);
    }

    /** @test */
    public function it_converts_pounds_to_kilograms()
    {
        $result = $this->service->convertMass(10, 'lbs', 'kg');
        $this->assertEqualsWithDelta(4.54, $result, 0.01);
    }

    /** @test */
    public function it_converts_square_meters_to_square_feet()
    {
        $result = $this->service->convertArea(10, 'sqm', 'sqft');
        $this->assertEqualsWithDelta(107.64, $result, 0.01);
    }

    /** @test */
    public function it_converts_square_feet_to_square_meters()
    {
        $result = $this->service->convertArea(100, 'sqft', 'sqm');
        $this->assertEqualsWithDelta(9.29, $result, 0.01);
    }

    /** @test */
    public function it_returns_same_value_for_same_unit()
    {
        $result = $this->service->convertLength(100, 'km', 'km');
        $this->assertEquals(100, $result);
    }

    /** @test */
    public function it_formats_value_with_label()
    {
        $formatted = $this->service->format(10.5, 'km');
        $this->assertStringContainsString('10.50', $formatted);
        $this->assertStringContainsString('Kilometers', $formatted);
    }

    /** @test */
    public function it_formats_value_with_short_label()
    {
        $formatted = $this->service->formatShort(10.5, 'km');
        $this->assertStringContainsString('10.50', $formatted);
        $this->assertStringContainsString('km', $formatted);
    }

    /** @test */
    public function it_gets_available_units_for_type()
    {
        $lengthUnits = $this->service->getAvailableUnits('length');
        $this->assertIsArray($lengthUnits);
        $this->assertArrayHasKey('km', $lengthUnits);
        $this->assertArrayHasKey('mi', $lengthUnits);
    }

    /** @test */
    public function it_gets_base_unit_for_type()
    {
        $baseUnit = $this->service->getBaseUnit('length');
        $this->assertEquals('km', $baseUnit);

        $baseUnit = $this->service->getBaseUnit('mass');
        $this->assertEquals('kg', $baseUnit);

        $baseUnit = $this->service->getBaseUnit('area');
        $this->assertEquals('sqm', $baseUnit);
    }

    /** @test */
    public function it_validates_units()
    {
        $this->assertTrue($this->service->isValidUnit('length', 'km'));
        $this->assertTrue($this->service->isValidUnit('mass', 'kg'));
        $this->assertFalse($this->service->isValidUnit('length', 'invalid'));
    }

    /** @test */
    public function it_gets_unit_types()
    {
        $types = $this->service->getUnitTypes();
        $this->assertIsArray($types);
        $this->assertContains('length', $types);
        $this->assertContains('mass', $types);
        $this->assertContains('area', $types);
    }

    /** @test */
    public function it_checks_if_enabled()
    {
        $enabled = $this->service->isEnabled();
        $this->assertIsBool($enabled);
    }

    /** @test */
    public function it_gets_display_label()
    {
        $label = $this->service->getLabel('km');
        $this->assertEquals('Kilometers', $label);

        $shortLabel = $this->service->getLabel('km', true);
        $this->assertEquals('km', $shortLabel);
    }
}
