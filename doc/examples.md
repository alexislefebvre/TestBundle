Examples
========

Fixtures
--------

The bundle's internal tests show several ways to load fixtures:

- [data with fixtures dependencies](../tests/App/DataFixtures/ORM/LoadDependentUserData.php)
- [data with dependency injection](../tests/App/DataFixtures/ORM/LoadUserWithServiceData.php)
- [fixture loading with Alice](../tests/App/DataFixtures/ORM/user.yml)
- custom provider:
  - [fixture to load](../tests/App/DataFixtures/ORM/user_with_custom_provider.yml)
  - [custom provider](../tests/AppConfig/DataFixtures/Faker/Provider/FooProvider.php)
  - [service declaration](../tests/AppConfig/config.yml)

Unit test
---------

```php
<?php

declare(strict_types=1);

namespace Liip\FooBundle\Tests;

use AlexisLefebvre\TestBundle\Controller\DefaultController;
use AlexisLefebvre\TestBundle\Test\WebTestCase;

class ExampleUnitTest extends WebTestCase
{
    /**
     * Example using LiipFunctionalBundle the service mock builder.
     */
    public function testIndexAction(): void
    {
        $view = $this->getServiceMockBuilder('FooView')->getMock();

        $view->expects($this->once())
            ->method('setTemplate')
            ->with('FooBundle:Default:index.twig')
            ->will($this->returnValue(null))
        ;

        $view->expects($this->once())
            ->method('handle')
            ->with()
            ->will($this->returnValue('success'))
        ;

        $controller = new DefaultController($view);

        $this->assertSame('success', $controller->indexAction());
    }
}
```

Functional test
---------------

```php
<?php

declare(strict_types=1);

namespace Liip\FooBundle\Tests;

use AlexisLefebvre\TestBundle\Test\WebTestCase;

class ExampleFunctionalTest extends WebTestCase
{
    /**
     * Example using LiipFunctionalBundle the fixture loader.
     */
    public function testUserFooIndex(): void
    {
        $this->loadFixtures(['Liip\FooBundle\Tests\Fixtures\LoadUserData']);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/users/foo');
        $this->assertStatusCode(200, $client);

        $this->assertTrue($crawler->filter('html:contains("Email: foo@bar.com")')->count() > 0);
    }

    /**
     * Example using LiipFunctionalBundle WebTestCase helpers and with authentication.
     */
    public function testBasicAuthentication(): void
    {
        $this->loadFixtures(['Liip\FooBundle\Tests\Fixtures\LoadUserData']);

        $content = $this->fetchContent('/users/foo', 'GET', true);
        $this->assertSame('Hello foo!', $content);

        // check if the logout button is shown
        $this->assertContains('logout', $content);
    }

    public function test404Page(): void
    {
        $this->fetchContent('/asdasdas', 'GET', false, false);
    }

    public function testLoginPage(): void
    {
        $content = $this->fetchContent('/', 'GET', false);
        $this->assertContains('login', $content);
    }

    public function testValidationErrors(): void
    {
        $client = $this->makeClient(true);
        $crawler = $client->request('GET', '/users/1/edit');

        $client->submit($crawler->selectButton('Save')->form());

        $this->assertValidationErrors(['data.username', 'data.email'], $client->getContainer());
    }
}
```
