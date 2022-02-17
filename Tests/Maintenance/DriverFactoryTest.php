<?php

namespace INSYS\Bundle\MaintenanceBundle\Tests\Maintenance;

use ErrorException;
use INSYS\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test driver factory
 *
 * @package INSYSMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactoryTest extends TestCase
{
    protected $factory;
    protected $container;

    public function setUp(): void
    {
        $driverOptions = array(
            'class' => '\INSYS\Bundle\MaintenanceBundle\Drivers\FileDriver',
            'options' => array('file_path' => sys_get_temp_dir().'/lock'));

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('insys_maintenance.driver.factory', $this->factory);
    }

    protected function tearDown(): void
    {
        $this->factory = null;
    }

    public function testDriver()
    {
        $driver = $this->factory->getDriver();
        $this->assertInstanceOf('\INSYS\Bundle\MaintenanceBundle\Drivers\FileDriver', $driver);
    }

    public function testExceptionConstructor()
    {
        $driver = $this->getDatabaseDriver();
        $translator = $this->getTranslator();

        $this->expectException(ErrorException::class);

        new DriverFactory($driver, $translator, array());
    }

    public function testWithDatabaseChoice()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);

        $this->container->set('insys_maintenance.driver.factory', $factory);

        $this->assertInstanceOf('INSYS\Bundle\MaintenanceBundle\Drivers\DatabaseDriver', $factory->getDriver());
    }

    public function testExceptionGetDriver()
    {
        $driverOptions = array('class' => '\Unknown', 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('insys_maintenance.driver.factory', $factory);

        $this->expectException(ErrorException::class);

        $factory->getDriver();
    }

    protected function initContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'          => false,
            'kernel.bundles'        => array('MaintenanceBundle' => 'INSYS\Bundle\MaintenanceBundle'),
            'kernel.cache_dir'      => sys_get_temp_dir(),
            'kernel.environment'    => 'dev',
            'kernel.root_dir'       => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        )));

        return $container;
    }

    protected function getDatabaseDriver()
    {
        $db = $this->getMockbuilder('INSYS\Bundle\MaintenanceBundle\Drivers\DatabaseDriver')
                ->disableOriginalConstructor()
                ->getMock();

        return $db;
    }

    public function getTranslator()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return $translator;
    }
}
