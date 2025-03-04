<?php
use PHPUnit\Framework\TestCase;

class JsonToOrganizationTest extends TestCase
{
    public function testType()
    {
        $type = new \Oscar\Entity\OrganizationType();
        $type->setLabel("Type d'organisation");
        $types = ["foo" => $type];
        $json = new \Oscar\Factory\JsonToOrganization($types);

        $this->assertNull($json->getTypeObj(null), 'Les types NULL retournent NULL');
        $this->assertNull($json->getTypeObj(""), 'Les types "chaîne vide" retournent NULL');
        $this->assertNull($json->getTypeObj("nexistepas"), 'Les types non référencés retournent NULL');

        $extractedType = $json->getTypeObj("foo");
        $this->assertEquals($extractedType, $type);
    }
}
