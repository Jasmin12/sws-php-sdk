<?php
namespace Serato\SwsSdk\Test;

use Serato\SwsSdk\Test\AbstractTestCase;
use Serato\SwsSdk\Sdk;
use InvalidArgumentException;

class SdkTest extends AbstractTestCase
{
    /**
     * @dataProvider invalidConstructorOptionsProvider
     */
    public function testInvalidConstructorOptions(array $args, $exceptionCode, $assertText)
    {
        try {
            $sdk = new Sdk($args, 'app_id', 'app_password');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals($e->getCode(), $exceptionCode, $assertText);
        }
    }

    public function invalidConstructorOptionsProvider()
    {
        return [
            [[], 1005, 'No `env` or `base_uri` provided'],
            [['timeout' => 2], 1000, 'Non-float value for `timeout`'],
            [['timeout' => 2.0, 'env' => 'invalid'], 1001, 'Invalid `env` value'],
            [['base_uri' => ''], 1002, 'Non-array `base_uri` value'],
            [['base_uri' => ['id' => 'value']], 1002, 'Missing `base_uri` `license` value'],
            [['base_uri' => ['license' => 'value']], 1002, 'Missing `base_uri` `id` value'],
            [
                ['base_uri' => ['id' => 'invald value', 'license' => 'http://my.server']],
                1003,
                'Invalid `base_uri` `id` value'
            ],
            [
                ['base_uri' => ['id' => 'http://my.server', 'license' => 'invald value']],
                1004,
                'Invalid `base_uri` `license` value'
            ],
            [
                ['env' => Sdk::ENV_PRODUCTION, 'handler' => 'not a callable'],
                1006,
                'Invalid `handler`, must be callable'
            ]
        ];
    }

    /**
     * @dataProvider validConstructorOptionsProvider
     */
    public function testValidConstructorOptions(
        array $args,
        $idServiceUri,
        $licenseServiceUri,
        $timeout,
        $handler,
        $assertText
    ) {
        $sdk = new Sdk($args, 'app_id', 'app_password');
        $config = $sdk->getConfig();

        $this->assertTrue(
            is_float($config['timeout']),
            $assertText . ' - Timeout is float'
        );
        if ($timeout !== null) {
            $this->assertEquals(
                $timeout,
                $config['timeout'],
                $assertText . ' - Custom timeout is correct'
            );
        }
        $this->assertTrue(
            is_array($config['base_uri']),
            $assertText . ' - `base_uri` option is array'
        );
        $this->assertEquals(
            $idServiceUri,
            $config['base_uri']['id'],
            $assertText . ' - `base_uri` `id` is correct'
        );
        $this->assertEquals(
            $licenseServiceUri,
            $config['base_uri']['license'],
            $assertText . ' - `base_uri` `license` is correct'
        );
        $this->assertEquals(
            $handler,
            $config['handler'],
            $assertText . ' - `handler` is correct'
        );
    }

    public function validConstructorOptionsProvider()
    {
        $idServiceUri       = 'http://id.server.com';
        $licenseServiceUri  = 'https://license.server.io';
        $handler            = function () {
        };

        return [
            [
                ['env' => Sdk::ENV_PRODUCTION],
                Sdk::BASE_URI_PRODUCTION_ID,
                Sdk::BASE_URI_PRODUCTION_LICENSE,
                null,
                null,
                'Set `env` to ENV_PRODUCTION'
            ],
            [
                ['env' => Sdk::ENV_STAGING],
                Sdk::BASE_URI_STAGING_ID,
                Sdk::BASE_URI_STAGING_LICENSE,
                null,
                null,
                'Set `env` to ENV_STAGING'
            ],
            [
                ['env' => Sdk::ENV_PRODUCTION, 'timeout' => 1.222],
                Sdk::BASE_URI_PRODUCTION_ID,
                Sdk::BASE_URI_PRODUCTION_LICENSE,
                1.222,
                null,
                'Set `env` to ENV_PRODUCTION and `timeout`'
            ],
            [
                ['env' => Sdk::ENV_STAGING, 'timeout' => 0.7622],
                Sdk::BASE_URI_STAGING_ID,
                Sdk::BASE_URI_STAGING_LICENSE,
                0.7622,
                null,
                'Set `env` to ENV_STAGING and `timeout`'
            ],
            [
                [
                    'base_uri' => ['id' => $idServiceUri, 'license' => $licenseServiceUri],
                    'timeout' => 3.2
                ],
                $idServiceUri,
                $licenseServiceUri,
                3.2,
                null,
                'Custom `base_uri` and `timeout`'
            ],
            [
                ['env' => Sdk::ENV_PRODUCTION, 'handler' => $handler],
                Sdk::BASE_URI_PRODUCTION_ID,
                Sdk::BASE_URI_PRODUCTION_LICENSE,
                null,
                $handler,
                'Set `env` to ENV_PRODUCTION, custom `handler`'
            ]
        ];
    }
}
