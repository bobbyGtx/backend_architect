<?php

require_once __DIR__ . '/../utils/validator-utils.php';
require_once __DIR__ . '/db-service.php';
enum AuthErrors: string {
    case emailNotRegistered = 'User with E-Mail is not registered.';
    case getSettingsError = 'Error retrieving settings from the database.';
    case authError = 'Authorisation error.';
    case secretKeyFieldNotFound = 'SecretKey field not found in database.';
    case passwordWrong = 'Password does not match.';
}
enum UserTblFields: string {
    case UserId='id';
    case Email='email';
    case Password='password';
    case Blocked='blocked';
    case Verification='emailVerification';
}

/**
 * Класс AuthService
 *
 * Отвечает за аутентификацию пользователей, расшифровку паролей,
 * генерацию токенов и валидацию данных входа.
 *
 * Использует родительский класс DbService для взаимодействия с базой данных.
 *
 */
class AuthService extends DBService {

    private string|null $secretKey=null;

    /**
     * Функция для разового запроса ключа шифрования паролей из БД.
     * При повторном обращении - возвращает из переменной $secretKey
     *
     * @return string с ключом для расшифровки паролей пользователей
     * @throws Exception Ошибка приходит из функции __getSettingsFromDB()
     */
    private function getSecretKey():string{
        if ($this->secretKey === null)$this->secretKey=$this->__getSettingsFromDB();
        return $this->secretKey;
        }

        /**
         * Детальное описание:
         * - Авторизует пользователя если пароль и E-Mail совпадают
         * - Проверяет E-Mail и Password на валидность.
         * - Возвращает идентификатор пользователя при успешном логине;
         *
         * @param string $email пользователя;
         * @param string $password пароль;
         * @return  integer Идентификатор пользователя в таблице БД. Всегда больше 0.
         * @throws Exception При ошибке при подготовке запроса
         * @throws Exception При ошибке из БД при выполнении запроса или обработке ответа
         * @throws Exception Если такой E-Mail не найден в базе данных
         * @throws Exception При несоответствии введенного пароля и пароля из БД
         */
    public function login(string $email, string $password): int {
        if (!ValidatorUtils::validateEmail($email)) throw new Exception(ValidationErrors::emailNotValid->value, 401);
        if (!ValidatorUtils::validatePassword($password)) throw new Exception(ValidationErrors::wrongPassword->value, 401);

        $userIdField = UserTblFields::UserId->value;
        $emailField = UserTblFields::Email->value;
        $passwordField = UserTblFields::Password->value;
        $blockedField = UserTblFields::Blocked->value;
        $verificationField = UserTblFields::Verification->value;

        $sql ="SELECT $userIdField,$emailField,$passwordField,$verificationField,$blockedField FROM users WHERE $emailField = ?";
        $link = $this->getConnection();
        try{
            $stmt = $link->prepare($sql);
            if (!$stmt) throw new Exception( DBSettings::DEBUG?$link->error:DBErrors::RequestRejected,500);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $response = $stmt->get_result();
            $numRows = $response->num_rows;
            $stmt->close();
        } catch (Exception $e) {throw new Exception(DBSettings::DEBUG?$e->getMessage():AuthErrors::authError->value,500);}

        if (empty($numRows)) throw new Exception(AuthErrors::emailNotRegistered->value,401);
        $record = $response->fetch_assoc();
        if ($password !== BaseUtils::decode($record[$passwordField],$this->getSecretKey())) throw new Exception(ValidationErrors::wrongPassword->value,401);
        return $record[$userIdField];
    }

    /**
     * Получает ключ шифрования паролей пользователей из таблицы settings.
     *
     * @return string Ключ шифрования.
     * @throws Exception Если подготовка запроса прошла с ошибкой.
     * @throws Exception Если запрос прошел с ошибкой.
     */
    private function __getSettingsFromDB():string {
        $settingsFieldKey ="secretKey" ;
        $sql="SELECT $settingsFieldKey FROM `settings`;";
        $link = $this->getConnection();
        try{
            $stmt = $link->prepare($sql);
            if (!$stmt) throw new Exception( DBSettings::DEBUG?$link->error:AuthErrors::getSettingsError->value,500);
            $stmt->execute();
            $response = $stmt->get_result();
            $numRows = $response->num_rows;
            $stmt->close();
        } catch (Exception $e) {throw new Exception(DBSettings::DEBUG?$e->getMessage():AuthErrors::getSettingsError->value,500);}
        if (empty($numRows)) throw new Exception(AuthErrors::getSettingsError->value,500);
        $row = $response->fetch_array();
        //if (!isset($row[$settingsFieldKey])) throw new Exception(AuthErrors::getSettingsError->value,500);

        return $row[$settingsFieldKey];
    }
}
