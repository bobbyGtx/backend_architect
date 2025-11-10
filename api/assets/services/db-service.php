<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../../../db-settings.php';
require_once __DIR__ . '/../utils/base-utils.php';

enum DBErrors: string {
    case ConnectionError = 'DB connection Error! ';
    case ConnectionInterrupt = 'Connection with DB interrupt. ';
    case RequestSettings = 'Error while requesting settings from DB. ';//ошибка получения настроек из базы данных
    case RequestRejected='Request rejected by database. ';
    case SelectReqRejected='Request (SELECT) rejected by database. ';
    case InsertReqRejected='Request (INSERT) rejected by database. ';
    case UpdateReqRejected='Request (UPDATE) rejected by database. ';
    case DeleteReqRejected='Request (DELETE) rejected by database. ';
    case RecognizeDataError = 'Unable to recognize data from database! ';
    case UnexpectedResponse = 'Unexpected response from Database! ';

}

abstract class DBService {
    private ?mysqli $link = null;

    /** @throws Exception */
    private function dbConnect():object {
        $password = BaseUtils::decode(DBSettings::PASSWORD,DBSettings::KEY);
        try{
            $link = mysqli_connect(DBSettings::HOST, DBSettings::LOGIN, $password, DBSettings::DBNAME);
            mysqli_set_charset($link, DBSettings::CHARSET);//Кодировка БД
            DBSettings::DEBUG && mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);// Включаем генерацию ошибок для нормальной работы try catch
        }catch(Exception $e){
            $message=DBSettings::DEBUG?$e->getMessage():DBErrors::ConnectionError;
            throw new Exception($message, 500);
        }

        return $link;
    }

    /** @throws Exception */
    protected function getConnection(): mysqli {
        if ($this->link === null) {$this->link=$this->dbConnect();}
        return $this->link;
    }//Ленивая загрузка с обработкой ошибок

    public function closeConnection(): void {
        mysqli_close($this->link);
    }
    public function __destruct() {
        if ($this->link instanceof mysqli) $this->link->close();
    }
}


