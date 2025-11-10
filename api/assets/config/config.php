<?php
enum BaseErrors:string{
    case MethodNotAllowed = "Request method Not Allowed!";
}
class Config{
    public static int $accessTokenLength = 100;
    public static int $accessTokenLife = 100000;
    public static int $refreshTokenLength = 120;
    public static int $refreshTokenLife = 2629743;
    public static string $emailRegEx = '/^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/iu';
    public static string $passwordRegEx = '/^.{6,}$/';
    public static string $telephoneRegEx = '/^\+[1-9]\d{1,14}$/iu';//+14155552671, +497116666777
    public static string $firstNameRegEx = '/^(?=.{2,50}$)([A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+(?:-[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+)*(?:\s[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+(?:-[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+)*)*)$/';
    public static string $lastNameRegEx = '/^(?=.{2,50}$)([A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+(?:-[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+)*(?:\s[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+(?:-[A-ZА-ЯЁÄÖÜ][a-zа-яёßäöü]+)*)*)$/';
    public static string $zipCodeRegEx = '/^[0-9]{5}$/';
    public static array $regionsD = ['Baden-Württemberg','Bayern','Berlin','Brandenburg','Bremen','Hamburg','Hessen','Mecklenburg-Vorpommern','Niedersachsen','Nordrhein-Westfalen','Rheinland-Pfalz','Saarland','Sachsen','Sachsen-Anhalt','Schleswig-Holstein','Thüringen'];
    public static function accessTokenRegEx(): string {
        return '/^[a-zA-Z0-9]{' . self::$accessTokenLength . '}$/';
    }
    public static function refreshTokenRegEx(): string {
        return '/^[a-zA-Z0-9]{' . self::$refreshTokenLength . '}$/';
    }
    public static function defaultErrorResponse(string $message, $debug=null): array {
        $response = ["error"=>true,"message"=>!empty($message)?$message:'Unknown error'];
        !empty($debug) && $response['debug'] = $debug;
        return $response;
    }//Формат ошибки по умолчанию
    public static function defaultResponse(string $message, array|null $data=null): array {
        $response = ["error"=>false,"message"=>$message];
        !is_null($data) && $response = array_merge($response, $data);
        return $response;
    }//Формат ответа по умолчанию

}