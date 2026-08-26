<?php

declare(strict_types=1);

namespace app\controllers;

use app\entities\CarOptionEntity;
use app\services\CarService;
use yii\web\BadRequestHttpException;

/**
 * REST API контроллер объявлений автомобилей.
 */
final class CarController extends BaseController
{
    /**
     * POST /car/create
     */
    public function actionCreate(): array
    {
        $body = $this->getBody();

        try {
            $car = $this->carService->create($body);
        } catch (\app\exceptions\ValidationException $e) {
            return $this->errorResponse(422, 'Unprocessable Entity', $e->getFieldErrors());
        }

        return $this->createdResponse($car->toArray());
    }

    /**
     * GET /car/{id}
     */
    public function actionView(int $id): array
    {
        $car = $this->carService->findById($id);

        if ($car === null) {
            throw new \yii\web\NotFoundHttpException('Объявление не найдено.');
        }

        return $this->okResponse($car->toArray());
    }

    /**
     * GET /car/list
     */
    public function actionIndex(int $page = 1): array
    {
        $pageSize = (int) (\Yii::$app->params['paginationPageSize'] ?? 20);

        $provider = $this->carService->findAll($page, $pageSize);

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
            throw new BadRequestHttpException('Некорректное тело запроса.');
        }

        return $body;
    }
}