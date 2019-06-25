<?php

namespace Larafortp\Tests\Features;

use Faker\Factory as FakerFactory;
use Larafortp\Tests\TestCase;

class FakerTest extends TestCase
{
    public function testZhcn()
    {
        $faker = FakerFactory::create('zh_CN');
        $word = $faker->realText(10);
        $result = preg_match('/[\x{4e00}-\x{9fa5}]/u', $word) ? true : false;
        $this->assertTrue($result);
    }

    public function testImage()
    {
        $faker = FakerFactory::create('zh_CN');
        $imageUrl = $faker->imageUrl('1903', '800', 'nature', true, 'sepia');
        $result = preg_match('/http\:\/\/placeimg\.com\/1903\/800\/nature\/sepia\?\d{5}/i', $imageUrl) ? true : false;
        $this->assertTrue($result);
    }
}
