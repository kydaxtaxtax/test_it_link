<?php

declare(strict_types=1);

namespace app\controllers;

use app\entities\CarOptionEntity;
use app\exceptions\ValidationException;
use app\services\CarService;
use app\validation\CarCreateValidator;
use app\validation\CarListValidator;
use app\validation\CarViewValidator;
use yii\base\Action;
use yii\web\BadRequestHttpException;

/**
 * REST API контроллер объявлений автомобилей.
 */
final class CarController extends BaseController
{
    private CarCreateValidator $createValidator;
    private CarViewValidator $viewValidator;
    private CarListValidator $listValidator;

    public function init(): void
    {
        parent::init();
        $this->createValidator = new CarCreateValidator();
        $this->viewValidator = new CarViewValidator();
        $this->listValidator = new CarListValidator();
    }

    /**
     * POST /car/create
     */
    public function actionCreate(): array
    {
        $body = $this->getBody();

        $errors = $this->createValidator->validate($body);
        if ($errors !== []) {
            $this->response->setStatusCode(422, 'Unprocessable Entity');
            return $this->errorResponse(422, 'Validation Error', $errors);
        }

        try {
            $car = $this->carService->create($body);
        } catch (ValidationException $e) {
            $this->response->setStatusCode(422, 'Unprocessable Entity');
            return $this->errorResponse(422, 'Validation Error', $e->getFieldErrors());
        }

        return $this->createdResponse($car->toArray());
    }

    /**
     * GET /car/{id}
     */
    public function actionView(int|string|null $id): array
    {
        $errors = $this->viewValidator->validate(['id' => $id]);
        if ($errors !== []) {
            $this->response->setStatusCode(404, 'Not Found');
            return $this->errorResponse(404, 'Not Found', $errors);
        }

        $car = $this->carService->findById((int) $id);

        if ($car === null) {
            $this->response->setStatusCode(404, 'Not Found');
            return $this->errorResponse(404, 'Not Found', ['id' => ['Объявление не найдено.']]);
        }

        return $this->okResponse($car->toArray());
    }

    /**
     * GET /car/list
     */
    public function actionIndex(int|string|null $page = 1, int|string|null $pageSize = null): array
    {
        $params = [];
        if ($page !== null) {
            $params['page'] = $page;
        }
        if ($pageSize !== null) {
            $params['pageSize'] = $pageSize;
        }

        $errors = $this->listValidator->validate($params);
        if ($errors !== []) {
            $this->response->setStatusCode(400, 'Bad Request');
            return $this->errorResponse(400, 'Bad Request', $errors);
        }

        $effectivePage = $page !== null && is_numeric($page) ? (int) $page : 1;
        $effectivePageSize = $pageSize !== null && is_numeric($pageSize) ? (int) $pageSize : (int) (\Yii::$app->params['paginationPageSize'] ?? 20);

        $provider = $this->carService->findAll($effectivePage, $effectivePageSize);

        $items = [];
        foreach ($provider->getModels() as $car) {
            $items[] = $car->toArray();
        }

        $pagination = $provider->getPagination();

        return $this->okResponse([
            'items' => $items,
            'pagination' => [
                'page' => $pagination->getPage() + 1,
                'pageSize' => $pagination->getPageSize(),
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getBody(): array
    {
        $body = \Yii::$app->request->getBodyParams();

        if (!is_array($body)) {
            throw new BadRequestHttpException('Некорректное тело запроса. Ожидался JSON объект.');
        }

        return $body;
    }
}