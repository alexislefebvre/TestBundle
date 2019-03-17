<?php

declare(strict_types=1);

/*
 * This file is part of the Liip/FunctionalTestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace AlexisLefebvre\TestBundle\Tests\Test;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use AlexisLefebvre\TestBundle\Annotations\QueryCount;
use AlexisLefebvre\TestBundle\Test\WebTestCase;
use AlexisLefebvre\TestBundle\Tests\AppConfig\AppConfigKernel;

/**
 * Tests that configuration has been loaded and users can be logged in.
 *
 * Use Tests/AppConfig/AppConfigKernel.php instead of
 * Tests/App/AppKernel.php.
 * So it must be loaded in a separate process.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * Avoid conflict with PHPUnit annotation when reading QueryCount
 * annotation:
 *
 * @IgnoreAnnotation("expectedException")
 */
class WebTestCaseConfigTest extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client client */
    private $client = null;

    protected static function getKernelClass(): string
    {
        return AppConfigKernel::class;
    }

    /**
     * Log in as an user.
     */
    public function testIndexAuthenticationArray(): void
    {
        $this->client = static::makeClient([
            'username' => 'foobar',
            'password' => '12341234',
        ]);

        $path = '/';

        $crawler = $this->client->request('GET', $path);

        $this->assertStatusCode(200, $this->client);

        $this->assertSame(1,
            $crawler->filter('html > body')->count());

        $this->assertSame(
            'Logged in as foobar.',
            $crawler->filter('p#user')->text()
        );

        $this->assertSame(
            'AlexisLefebvreTestBundle',
            $crawler->filter('h1')->text()
        );
    }

    /**
     * Log in as the user defined in the
     * "alexis_lefebvre_test.authentication"
     * node from the configuration file.
     */
    public function testIndexAuthenticationTrue(): void
    {
        $this->client = static::makeClient(true);

        $path = '/';

        $crawler = $this->client->request('GET', $path);

        $this->assertStatusCode(200, $this->client);

        $this->assertSame(1,
            $crawler->filter('html > body')->count());

        $this->assertSame(
            'Logged in as foobar.',
            $crawler->filter('p#user')->text()
        );

        $this->assertSame(
            'AlexisLefebvreTestBundle',
            $crawler->filter('h1')->text()
        );
    }

    /**
     * Log in as the user defined in the Data Fixture.
     */
    public function testIndexAuthenticationLoginAs(): void
    {
//        $fixtures = $this->loadFixtures([
//            'AlexisLefebvre\TestBundle\Tests\App\DataFixtures\ORM\LoadUserData',
//        ]);
//
//        /** @var \Doctrine\Common\DataFixtures\ReferenceRepository $repository */
//        $repository = $fixtures->getReferenceRepository();
//
//        $loginAs = $this->loginAs($repository->getReference('user'),
//            'secured_area');
//
//        $this->assertInstanceOf(
//            'AlexisLefebvre\TestBundle\Test\WebTestCase',
//            $loginAs
//        );

        $this->client = static::makeClient();

        $path = '/';

        $crawler = $this->client->request('GET', $path);

        $this->assertStatusCode(200, $this->client);

        $this->assertSame(1,
            $crawler->filter('html > body')->count());

        $this->assertSame(
            'Logged in as foo bar.',
            $crawler->filter('p#user')->text()
        );

        $this->assertSame(
            'AlexisLefebvreTestBundle',
            $crawler->filter('h1')->text()
        );
    }

    /**
     * Log in as the user defined in the Data Fixtures and except an
     * AllowedQueriesExceededException exception.
     *
     * There will be 2 queries, in the configuration the limit is 1,
     * an Exception will be thrown.
     *
     * @expectedException \AlexisLefebvre\TestBundle\Exception\AllowedQueriesExceededException
     */
    public function testAllowedQueriesExceededException(): void
    {
        $this->client = static::makeClient();

        // One another query to load the second user.
        $path = '/user/2';

        $this->client->request('GET', $path);
    }

    /**
     * Expect an exception due to the QueryCount annotation.
     *
     * @QueryCount(0)
     *
     * There will be 1 query, in the annotation the limit is 0,
     * an Exception will be thrown.
     *
     * @expectedException \AlexisLefebvre\TestBundle\Exception\AllowedQueriesExceededException
     */
    public function testAnnotationAndException(): void
    {
        $this->client = static::makeClient();

        // One query to load the second user
        $path = '/user/1';

        $this->client->request('GET', $path);
    }
}
