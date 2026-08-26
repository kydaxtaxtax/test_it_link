<?php

declare(strict_types=1);

namespace app\controllers;

use app\exceptions\ValidationException;
use yii\base\Action;
use yii\rest\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Базовый REST контроллер с общей обёрткой ответов.
 */
abstract class BaseController extends Controller
{
    protected CarService $carService;

    public function init(): void
    {
        parent::init();
        $this->carService = \Yii::$container->get(\app\services\CarService::class);
    }

    /**
     * Выполняет действие с общей обёрткой ошибок.
     * Перехватывает ValidationException (422), NotFoundHttpException (404),
     * BadRequestHttpException (400) и общие \Throwable (500).
     */
    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (ValidationException $e) {
            return $this->errorResponse(422, 'Unprocessable Entity', $e->getFieldErrors());
        } catch (HttpException $e) {
            $errors = [];
            if ($e instanceof \yii\web\NotFoundHttpException) {
                $errors = ['detail' => $e->getMessage()];
            } else {
                $errors = ['detail' => $e->getMessage()];
            }
            return $this->errorResponse($e->statusCode, $e->getName(), $errors);
        } catch (\Throwable $e) {
            \Yii::error('Unhandled exception: ' . $e->getMessage(), __METHOD__);
            return $this->errorResponse(500, 'Internal Server Error', ['detail' => 'Внутренняя ошибка сервера.']);
        }
    }

    /**
     * Возвращает стандартизированный ответ ошибки.
     *
     * @param int $status
     * @param string $title
     * @param array<string, mixed> $errors
     * @return array<string, mixed>
     */
    protected function errorResponse(int $status, string $title, array $errors): array
    {
        $response = \Yii::$app->response;
        $response->setStatusCode($status);
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');

        $body = ['status' => $status, 'title' => $title];
        if (count($errors) === 1 && isset($errors['detail'])) {
            $body['detail'] = $errors['detail'];
        } else {
            $body['errors'] = $errors;
        }

        return $body;
    }

    /**
     * Возвращает ответ 201 Created с данными.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function createdResponse(array $data): array
    {
        $response = \Yii::$app->response;
        $response->setStatusCode(201, 'Created');
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');

        return $data;
    }

    /**
     * Возвращает ответ 200 OK с данными.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function okResponse(array $data): array
    {
        $response = \Yii::$app->response;
        $response->setStatusCode(200, 'OK');
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');

        return $data;
    }
}