<?php

/*
 * This file is part of Laravel Credentials.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\Credentials\Facades;

use GrahamCampbell\TestBench\Traits\FacadeTestCaseTrait;
use GrahamCampbell\Tests\Credentials\AbstractTestCase;

/**
 * This is the credentials facade test class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 */
class CredentialsTest extends AbstractTestCase
{
    use FacadeTestCaseTrait;

    /**
     * Get the facade accessor.
     *
     * @return string
     */
    protected function getFacadeAccessor()
    {
        return 'credentials';
    }

    /**
     * Get the facade class.
     *
     * @return string
     */
    protected function getFacadeClass()
    {
        return 'GrahamCampbell\Credentials\Facades\Credentials';
    }

    /**
     * Get the facade route.
     *
     * @return string
     */
    protected function getFacadeRoot()
    {
        return 'GrahamCampbell\Credentials\Credentials';
    }
}
