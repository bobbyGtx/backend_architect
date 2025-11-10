<?php
require_once __DIR__ . '/../config/config.php';
enum ValidationErrors: string {
    case emailNotValid = 'Email address is not valid. ';
    case emptyEmail = 'Empty E-Mail is not valid. ';
    case emptyPassword = 'Empty password is not valid. ';
    case wrongPassword = 'Password is wrong. ';

}
class ValidatorUtils {
    public static function validateAccessToken(string $aToken): bool {
        return preg_match(Config::accessTokenRegex(), $aToken) === 1;
    }
    public static function validateRefreshToken(string $rToken): bool {
        return preg_match(Config::refreshTokenRegEx(), $rToken) === 1;
    }
    public static function validateEmail(string $email): bool {
        return preg_match(Config::$emailRegEx, $email) === 1;
    }
    public static function validatePassword(string $password): bool {
        return preg_match(Config::$passwordRegEx, $password) === 1;
    }
    public static function validateTelephone(string $telephone): bool {
        return preg_match(Config::$telephoneRegEx, $telephone) === 1;
    }
    public static function validateFirstName(string $firstName): bool {
        return preg_match(Config::$firstNameRegEx, $firstName) === 1;
    }
    public static function validateLastName(string $lastName): bool {
        return preg_match(Config::$firstNameRegEx, $lastName) === 1;
    }
    public static function validateZipCode(string $zipCode): bool {
        return preg_match(Config::$firstNameRegEx, $zipCode) === 1;
    }
    public static function validateRegion(string $region): bool {
        return in_array($region, Config::$regionsD);
    }
}