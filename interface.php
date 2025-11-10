<?php

interface Button{
    public function on();
    public function off();
}//Описывает обязательные методы у классов. Класс может имплементировать несколько интерфейсов
interface Color{
    public function on();
    public function colorOn();
    public function off();
    public function colorOff();
}//Описывает обязательные методы у классов. Класс может имплементировать несколько интерфейсов

class LightButton implements Button{
    public function on(){
        echo "Свет включен!\n";
    }
    public function off(){
        echo "Свет выключен!\n";
    }
}
class ComputerButton implements Button, Color{
    public function on():void{
        echo "Компьютер включен!";
        $this->colorOn();
    }
    public function colorOn():void{
        echo "Подсветка включена!\n";
    }
    public function off():void{
        echo "Компьютер выключен!";
        $this->colorOff();
    }
    public function colorOff():void{
        echo "Подсветка выключена!\n";
    }
}

$lightButton = new LightButton();
actionWithButton($lightButton);
$computerButton = new ComputerButton();
actionWithButton($computerButton);

function actionWithButton (Button $button):void{
    $button->on();
    $button->off();
}