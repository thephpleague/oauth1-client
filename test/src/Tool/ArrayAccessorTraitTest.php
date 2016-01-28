<?php
/**
 * This file is part of the league/oauth1-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Tests\Tool;

use League\OAuth1\Client\Tool\ArrayAccessorTrait;
use PHPUnit_Framework_TestCase;

class ArrayAccessorTraitTest extends PHPUnit_Framework_TestCase
{
    use ArrayAccessorTrait;

    public function testGetRootValue()
    {
        $array = ['foo' => 'bar'];

        $result = static::getValueByKey($array, 'foo');

        $this->assertEquals($array['foo'], $result);
    }

    public function testGetNonExistentValueWithDefault()
    {
        $array = [];
        $default = 'foo';

        $result = static::getValueByKey($array, 'bar', $default);

        $this->assertEquals($default, $result);
    }

    public function testGetNestedValue()
    {
        $array = ['foo' => ['bar' => 'murray']];

        $result = static::getValueByKey($array, 'foo.bar');

        $this->assertEquals($array['foo']['bar'], $result);
    }

    public function testGetNonExistantRootValue()
    {
        $array = ['foo' => 'bar'];

        $result = static::getValueByKey($array, 'foo.bar');

        $this->assertNull($result);
    }
}
