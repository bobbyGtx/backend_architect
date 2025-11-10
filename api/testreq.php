<?php

use JetBrains\PhpStorm\NoReturn;

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: OPTIONS, POST, PATCH, GET, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/assets/utils/base-utils.php';
require_once __DIR__ . '/assets/services/auth-service.php';
require_once __DIR__ . '/assets/config/config.php';
class TestRequest {
    private RequestMethod $requestMethod;
    private RequestLanguage $reqLanguage;
    public string $respMessage = 'Request successful.';
    private array $responsePostBody = ['selLanguage' => null, 'userId' => null,];
    private array $responseGetBody = ['selLanguage' => null, 'userId' => null, 'productsInCart' => []];
    private array | null $responseErrorBody = null;
    public int $userId = 0;
    private array $products = [
        ['id' => 1, 'product' => 'Апельсиновый сок'],
        ['id' => 2, 'product' => 'Шоколадное печенье'],
        ['id' => 3, 'product' => 'Молоко 2.5%'],
        ['id' => 4, 'product' => 'Сыр Чеддер'],
        ['id' => 5, 'product' => 'Хлеб пшеничный'],
        ['id' => 6, 'product' => 'Йогурт клубничный'],
        ['id' => 7, 'product' => 'Кофе молотый'],
        ['id' => 8, 'product' => 'Чай зелёный'],
        ['id' => 9, 'product' => 'Минеральная вода'],
        ['id' => 10, 'product' => 'Сливочное масло'],
        ['id' => 11, 'product' => 'Колбаса варёная'],
        ['id' => 12, 'product' => 'Макароны спагетти'],
        ['id' => 13, 'product' => 'Рис длиннозёрный'],
        ['id' => 14, 'product' => 'Картофель молодой'],
        ['id' => 15, 'product' => 'Яблоки красные'],
        ['id' => 16, 'product' => 'Бананы'],
        ['id' => 17, 'product' => 'Груши'],
        ['id' => 18, 'product' => 'Куриное филе'],
        ['id' => 19, 'product' => 'Говядина охлаждённая'],
        ['id' => 20, 'product' => 'Свинина'],
        ['id' => 21, 'product' => 'Лосось филе'],
        ['id' => 22, 'product' => 'Тунец консервированный'],
        ['id' => 23, 'product' => 'Мука пшеничная'],
        ['id' => 24, 'product' => 'Сахар-песок'],
        ['id' => 25, 'product' => 'Соль морская'],
        ['id' => 26, 'product' => 'Подсолнечное масло'],
        ['id' => 27, 'product' => 'Оливковое масло'],
        ['id' => 28, 'product' => 'Мёд натуральный'],
        ['id' => 29, 'product' => 'Кетчуп томатный'],
        ['id' => 30, 'product' => 'Майонез классический'],
        ['id' => 31, 'product' => 'Гречневая крупа'],
        ['id' => 32, 'product' => 'Овсяные хлопья'],
        ['id' => 33, 'product' => 'Кукурузные хлопья'],
        ['id' => 34, 'product' => 'Творог 5%'],
        ['id' => 35, 'product' => 'Сметана 15%'],
        ['id' => 36, 'product' => 'Соки ассорти'],
        ['id' => 37, 'product' => 'Печенье овсяное'],
        ['id' => 38, 'product' => 'Шоколад тёмный'],
        ['id' => 39, 'product' => 'Морковь'],
        ['id' => 40, 'product' => 'Огурцы свежие'],
    ];
    private array $userCarts = [
        ['id' => 1, 'userId' => 1, 'products' => [1, 17, 35, 34, 11]],
        ['id' => 2, 'userId' => 2, 'products' => [10, 33, 11, 5]],
        ['id' => 3, 'userId' => 3, 'products' => [35, 17, 21, 22, 40]],
    ];

    function __construct() {
        $this->requestMethod = BaseUtils::getRequestMethod($_SERVER['REQUEST_METHOD']);
        $this->reqLanguage = BaseUtils::getRequestLanguage(getallheaders());
        switch ($this->requestMethod) {
            case requestMethod::POST:$this->processPost();break;
            case requestMethod::GET:$this->processGet();break;
            default:$this->sendErrorResponse(BaseErrors::MethodNotAllowed->value,404);
        }
    }

    private function processPost(): void {
        $loginFieldKey = 'email';
        $passwordFieldKey = 'password';
        $incomingField = [
            ['field' => $loginFieldKey, 'type' => 'string', 'required' => true],
            ['field' => $passwordFieldKey, 'type' => 'string', 'required' => true],
        ];
        try {
            $incData = BaseUtils::getIncomingData($incomingField,DataSources::Body);
            $login = $incData[$loginFieldKey] ?? null;
            $password = $incData[$passwordFieldKey] ?? null;
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }

        $authService = new AuthService();
        try{
            $this->userId = $authService->login($login, $password);
        }catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }

        /*
        try {
            $this->userId = $this->login($login, $password);
            if ($this->userId === 0) $this->sendErrorResponse("User id not found", 500);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }*/

        $this->respMessage = 'User successfully logged in.';
        $this->responsePostBody['userId'] = $this->userId;
        $this->responsePostBody['selLanguage'] = $this->reqLanguage->name;
        $this->sendResponse();
    }

    private function processGet():void {
        $userIdKey = 'userId';
        $incomingField = [
            ['field' => $userIdKey, 'type' => 'int', 'required' => true],
            ['field' => 'types', 'type' => 'array', 'required' => true],
            ['field' => 'diameterFrom', 'type' => 'int', 'required' => true],
            ['field' => 'diameterTo', 'type' => 'int', 'required' => true],
            ['field' => 'heightFrom', 'type' => 'int', 'required' => true],
            ['field' => 'heightTo', 'type' => 'int', 'required' => true],
            ['field' => 'sort', 'type' => 'string', 'required' => true],
            ['field' => 'priceFrom', 'type' => 'int', 'required' => true],
            ['field' => 'priceTo', 'type' => 'int', 'required' => true],
            ['field' => 'page', 'type' => 'int', 'required' => true],
            ];
        try {
            $incData = BaseUtils::getIncomingData($incomingField,DataSources::URL);
            $this->userId = $incData[$userIdKey];
            $this->responseGetBody['debug'] = $incData;
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }
/*
        $userId = intval($_GET['userId']) ?? null;
        if (empty($userId)) $this->sendErrorResponse("User id not found", 400);
        $this->userId = $userId;*/
        try {
            $userCart = $this->getUserCart();
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }

        try {
            $productsList = $this->getProductList($userCart);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        }
        $this->responseGetBody['userId']=$this->userId;
        $this->responseGetBody['productsInCart']=$productsList;
        $this->responseGetBody['selLanguage']=$this->reqLanguage->name;

        $this->sendResponse();
    }

    function getUserCart(): array {
        if ($this->userId === 0) throw new Exception("User not found!", 500);
        $indexCart = array_search($this->userId, array_column($this->userCarts, 'userId'));
        if ($indexCart === false) throw new Exception("Cart for userid $this->userId not found!", 500);
        return $this->userCarts[$indexCart]['products'];
    }

    /** @throws Exception */
    function getProductList(array $productIds): array {
        if (count($productIds) === 0) return [];
        $productList = [];
        foreach ($productIds as $productId) {
            $productIndex = array_search($productId, array_column($this->products, 'id'));
            if ($productIndex === false) throw new Exception("Product $productId not found!", 400);
            $productList[] = $this->products[$productIndex]['product'];
        }
        return $productList;
    }

    #[NoReturn]
    function sendErrorResponse(string $message, int $code = 400, array|null $debugData = null): void {
        http_response_code($code);
        $responseError = Config::defaultErrorResponse($message,$debugData);
        is_array($this->responseErrorBody)??array_merge($responseError,$this->responseErrorBody);
        echo json_encode($responseError);
        exit;
    }

    #[NoReturn]
    private function sendResponse(int $code=200): void {
        //Если Options - то ответ будет "ок"
        http_response_code($code);
        if ($this->requestMethod === RequestMethod::POST) $response = Config::defaultResponse($this->respMessage,$this->responsePostBody);
        if ($this->requestMethod === RequestMethod::GET) $response = Config::defaultResponse($this->respMessage,$this->responseGetBody);

        if (isset($response)) echo json_encode($response);
        exit;
    }
}

new TestRequest();


