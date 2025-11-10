<?php

abstract class People {
    //Из абстрактного класса нельзя создать экземпляр
    private string $name;
    protected int $age;
    public string $address;
    private bool $ready = false;

    function __construct($name, $age,$address) {
        $this->name = $name;
        $this->age = $age;
        $this->address = $address;
        $this->sayHello();
    }

    function getReady(): string {
        return $this->ready?"Готов к работе!":"Я отдыхаю!\n";
    }
    function setReady(bool $ready): void {
        $this->ready = $ready;
    }

    static function workTime() {
        echo "Моё рабочее время с 8 до 16:30 \n";
    }

    function getName(): string {
        return $this->name;
    }
    function getAge(): int {
        return $this->age;
    }

    function sayHello() {
        echo "Привет, меня зовут $this->name и мне $this->age лет.! \n";
    }

    function cleaning($object): void {
        echo "Начинаю убирать: $object \n";
        echo "Убираю: $object \n";
        echo "Заканчиваю убирать: $object \n";
    }
}
class Workman extends People {
    private string $organisation;
    private string $department;
    public function __construct($name, $age,$address,$organisation, $department) {
        parent::__construct($name, $age, $address);
        $this->organisation = $organisation;
        $this->department = $department;
    }

    public function getInfo():void {
        $name = parent::getName();
        $age = $this->age;//Прямой доступ к защищенному полю у родителя
        $address = $this->address;//Прямой доступ к публичному полю у родителя
        echo "Меня зовут $name, мне $age. Я работаю в $this->organisation в отделе $this->department \n";

    }
}

//People::workTime();

$worker = new Workman('Владимир', 39, "Rubensstr. 10", "Майкрософт", "Маркетинг");
$worker->getInfo();
$worker->cleaning('Спальня');
$worker->cleaning('Кухня');


