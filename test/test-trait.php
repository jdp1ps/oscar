<?php
interface ITestTrait {
    public function getToto();
    public function setToto( $toto );
    public function compareToto( ITestTrait $toto );
}
trait TestTrait {
    private $toto;
    
    public function getToto()
    {
        return $this->toto;
    }
    
    public function setToto( $toto )
    {
        $this->toto = $toto;
    }
    
    public function compareToto(ITestTrait $testTraitUse )
    {
        return $testTraitUse->getToto() == $this->getToto();
    }
}


class A implements ITestTrait{
    use TestTrait;
}

class B implements ITestTrait{
    use TestTrait;
}

$a = new A();
$a->setToto("toto");

$b = new B();
$b->setToto("tata");

var_dump($a->compareToto($b));