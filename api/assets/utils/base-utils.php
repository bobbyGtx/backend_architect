<?php

require_once __DIR__ . '/../types/field-definition.class.php';

enum RequestLanguage: string {
    case ru = '';
    case en = '_en';
    case de = '_de';
}

enum RequestMethod {
    case OPTIONS;
    case GET;
    case POST;
    case PATCH;
    case DELETE;
    case PUT;
    case HEAD;
}

/** Варианты поиска входящих данных */
enum DataSources {
    /** Данные ожидаются в теле запроса */
    case Body;
    /** Данные ожидаются в URL (queryParams или params) */
    case URL;
}

enum HeaderKeys: string {
    case Language = 'x-language';
    case AccessToken = 'x-access-token';
    case RefreshToken = 'x-refresh-token';
}

abstract class BaseUtils {
    static function getRequestLanguage(array $reqHeaders): RequestLanguage {
        $defaultLanguage = RequestLanguage::ru;
        $languageKey = HeaderKeys::Language->value;
        if (empty($reqHeaders) || !isset($headers[$languageKey])) return $defaultLanguage;
        $headers = array_change_key_case($reqHeaders, CASE_LOWER);
        $lng = strtolower($headers[$languageKey]);
        return match ($lng) {
            'en' => RequestLanguage::en,
            'de' => RequestLanguage::de,
            default => $defaultLanguage,
        };
    }//Определение запрашиваемого языка

    static function getRequestMethod(string $method): RequestMethod {
        $requestMethod = null;
        switch ($method) {
            case 'OPTIONS':
                http_response_code(200);
                exit;
            case 'GET':
                $requestMethod = RequestMethod::GET;
                break;
            case 'POST':
                $requestMethod = RequestMethod::POST;
                break;
            case 'PATCH':
                $requestMethod = RequestMethod::PATCH;
                break;
            case 'DELETE':
                $requestMethod = RequestMethod::DELETE;
                break;
            case 'PUT':
                $requestMethod = RequestMethod::PUT;
                break;
            case 'HEAD':
                $requestMethod = RequestMethod::HEAD;
                break;
            default:
                $requestMethod = RequestMethod::OPTIONS;
        }
        return $requestMethod;

    }//Определение метода запроса

    /**
     * Детальное описание:
     * - Ищет запрошенные данные в теле запроса или url адресе (params и queryParams). Зависит от $dataSource
     * - Проверки: тип данных (string, integer, boolean, array), наличие обязательных данных, наличие данных в входящем массиве
     * - Тип данных array передаваемый через URL должен быть key[]=value. key=value не работает
     * - Возвращает массив параметров в формате 'ключ'->значение.
     *
     * @param $incomeFields array (ассоциативный массив или массив ассоциативных массивов):
     *  ['field' => 'password', 'type' => 'string','required' => true] или
     *  [['field' => 'login', 'type' => 'string','required' => true], ...]
     * @param $dataSource DataSources (ассоциативный массив или массив ассоциативных массивов)
     * @return array ассоциативный массив в формате 'ключ'->значение.
     * @throws Exception Входящий массив пуст
     */
    static function getIncomingData(array $incomeFields, DataSources $dataSource): array {
        $returnData = [];
        if (count($incomeFields) === 0) throw new Exception("Field list 'incomeFields' is empty!", 500);//Массив запрошенных полей пуст
        $incomingData = $dataSource === DataSources::URL ? $_GET : json_decode(file_get_contents('php://input'), true);

        $dataArray = [];//Данные для вывода
        $requiredFieldsDetected = 0;
        foreach ($incomeFields as $incomeField) {
            if (!($incomeField instanceof FieldDefinition)) throw new Exception("Wrong field list format!", 500);
            $incomeField->required && $requiredFieldsDetected++;
            if (array_key_exists($incomeField->field, $incomingData)) {
                $crudeData = $incomingData[$incomeField->field];
                $data = self::__checkIncDataType($crudeData, $incomeField->field, $incomeField->type);
                if (is_null($data) && $incomeField->required) throw new Exception("Field is required but null. [$incomeField->field]", 400);
                $dataArray = array_merge($dataArray, [$incomeField->field => $data]);
            } else {
                if ($incomeField->required) throw new Exception("Field is required but was not found in the request body. [$incomeField->field]", 400);
            }
        }

        if (count($dataArray) >= $requiredFieldsDetected) {
            $returnData = $dataArray;
        } else throw new Exception('Income data not found!', 400);

        endFunc:
        if (count($returnData) === 0) throw new Exception("Incoming data not found. Check 'incomeFields' variable!", 500);

        return $returnData;
    }

    static private function __checkIncDataType(mixed $crudeData, string $fieldName, DataType $fieldType): mixed {
        $data = null;
        switch ($fieldType) {
            case DataType::String:
                if (strlen($crudeData) > 0) $data = strval($crudeData);
                break;
            case DataType::Integer:
                $data = intval($crudeData);//Всегда 0 если левые данные
                break;
            case DataType::Float:
                if (!is_scalar($crudeData) || !preg_match('/^\d+[.,]?\d*$/', $crudeData)) {
                    throw new InvalidArgumentException("Field '$fieldName' is not a float!", 400);
                }
                $data = (float)str_replace(',', '.', $crudeData);
                break;
            case DataType::Boolean:
                if ($crudeData === '0' || $crudeData === 0 || $crudeData === 'false' || $crudeData === false) {
                    $data = false;
                } elseif ($crudeData === '1' || $crudeData === 1 || $crudeData === 'true' || $crudeData === true) {
                    $data = true;
                }
                break;
            case DataType::Array:
                if (is_array($crudeData) && count($crudeData) > 0) {
                    $data = $crudeData;
                } else throw new Exception("Type of field is not array! [$fieldName]", 400);
                break;
        }
        return $data;
    }

    static function decode(string $encoded, string $key): string {
        $strofsym = "qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
        $len = strlen($strofsym);
        // Подготовка карты "шаблон => символ"
        $replaceMap = [];
        for ($x = 0; $x < $len; $x++) {
            $char = $strofsym[$x];
            $hash = md5(md5($key . $char) . $key);
            $pattern = $hash[3] . $hash[6] . $hash[1] . $hash[2];
            $replaceMap[$pattern] = $char;
        }
        $decodedStr = strtr($encoded, $replaceMap);

        return base64_decode($decodedStr);
    }//Функция расшифровки строки

    static function encode(string $unencoded, string $key): string {
        $string = base64_encode($unencoded); // Переводим в base64
        $len = strlen($string);
        $chars = []; // Массив символов для новой строки

        for ($i = 0; $i < $len; $i++) {
            $hash = md5(md5($key . $string[$i]) . $key);
            $chars[] = $hash[3] . $hash[6] . $hash[1] . $hash[2];
        }

        return implode('', $chars);
    }//Функция шифрования строки

    static function generateTokens(): array {
        $accessToken = BaseUtils::generateString(100);
        $refreshToken = BaseUtils::generateString(120);
        return [$accessToken, $refreshToken];
    }

    static function generateString($strength = 16): string {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//строка допустимых символов
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

}