<?php
/** Варианты типов данных входящих данных */
enum DataType {
    /** Строка */
    case String;
    /** Целое число */
    case Integer;
    /** Число с плавающей запятой */
    case Float;
    /** Логическое значение */
    case Boolean;
    /** Массив */
    case Array;
}

/** Класс для передачи данных полей в функцию поиска входящих данных */
class FieldDefinition {
    /** @var string Название ключа, по которому искать данные. Например 'password' */
    public string $field;
    /** @var DataType тип ожидаемых данных. */
    public DataType $type;
    /** @var bool Флаг, указывающий на необходимость наличия данных. По умолчанию false*/
    public bool $required;

    public function __construct(string $field, DataType $type, bool $required = false) {
        $this->field = $field;
        $this->type = $type;
        $this->required = $required;
    }
}