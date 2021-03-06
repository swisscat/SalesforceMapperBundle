<?php

namespace Swisscat\SalesforceBundle\Test\Mapper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Swisscat\SalesforceBundle\Mapping\Driver\XmlDriver;
use Swisscat\SalesforceBundle\Mapping\Mapper;
use Swisscat\SalesforceBundle\Mapping\MappingException;
use Swisscat\SalesforceBundle\Test\TestCase;
use Swisscat\SalesforceBundle\Test\TestData\Customer;

class XmlMapperTest extends TestCase
{
    public function testFailureOnNonExistingDirectories()
    {
        $xmlDriver = new XmlDriver([dirname(__DIR__).'/TestDataNonExisting']);

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Could not find a mapping for class '".Customer::class."'");
        $xmlDriver->loadMetadataForClass(Customer::class);
    }

    public function testFailureOnMissingEntityManagerDeclaration()
    {
        $xmlDriver = new XmlDriver([dirname(__DIR__).'/TestData']);

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("The following configurations are missing for class ".Customer::class.": EntityManager");
        $xmlDriver->loadMetadataForClass(Customer::class);
    }

    public function testFailureOnMissingEntity()
    {
        $xmlDriver = new XmlDriver([dirname(__DIR__).'/TestData/Invalid']);
        $xmlDriver->setEntityManager($this->createMock(EntityManagerInterface::class));

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Could not find a mapping for class '".Customer::class."Toto'");
        $xmlDriver->loadMetadataForClass(Customer::class.'Toto');
    }

    public function testFailureOnMalformedXml()
    {
        $xmlDriver = new XmlDriver([dirname(__DIR__).'/TestData/Invalid']);

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("XML parse failure");
        $xmlDriver->loadMetadataForClass(Customer::class.'InvalidXml');
    }

    public function testFailureOnInvalidLocalMapping()
    {
        $xmlDriver = new XmlDriver([dirname(__DIR__).'/TestData/Invalid']);

        $mapper = new Mapper($xmlDriver, $this->createMock(EntityManager::class));

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Invalid mapping definition for class ".Customer::class.'InvalidLocalMapping'.': Invalid identification strategy');
        $mapper->getEntity(Customer::class.'InvalidLocalMapping', 'sf1234');
    }
}