<?php
namespace Tests\Dummy;

class DummyNoConstructor
{
    /**
     * @var int
     */
    private $age = 5;

    /**
     * @return void
     */
    public function test1()
    {
        //Do Nothing
    }

    /**
     * @param int $age
     * @return void
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }
}
