<?php

namespace App\Traits;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RestExceptionHandlerTrait
{

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, Exception $e)
    {
        error_log($e);

        switch(true) {
            case $this->isModelNotFoundException($e):
                $retval = $this->modelNotFound();
                break;
            case $this->isValidationException($e):
                $retval = $this->invalidData($e);
                break;
            case $e->getMessage() === 'Unauthenticated.':
                $retval = $this->unauthorizedUser();
                break;
            case $this->isDatabaseException($e):
                $retval = $this->databaseMapper($e);
                break;
            case $e instanceof NotFoundHttpException:
                $retval = $this->jsonResponse(['errorMessage' => 'Página não encontrada'], 404);
                break;
            case $e instanceof UnauthorizedException:
                $retval = $this->unauthorizedUser($e->getMessage());
                break;
            case $e instanceof AuthorizationException:
                $retval = $this->unauthorizedUser();
                break;
            default:
                $retval = $this->badRequest();
        }

        return $retval;
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($message='Bad request', $statusCode=400)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message='Record not found', $statusCode=404)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for unauthorized user exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedUser($message='Unauthorized user', $statusCode=401)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for unauthorized user exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidData(ValidationException $exception)
    {
        return $this->jsonResponse(['validationErrors' => $exception->errors()], 422);
    }

    /**
     * Returns json response for database exception.
     *
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function databaseMapper(QueryException $exception)
    {
        foreach (self::getMappedMessages() as $key => $value)
        {
            if (str_contains($exception->getMessage(), $key))
            {
                return $this->jsonResponse(['validationErrors' => ['messages' => [$value]]], 422);
            }
        }

        return $this->badRequest();
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload=null, $statusCode=404)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isModelNotFoundException(Exception $e)
    {
        return $e instanceof ModelNotFoundException;
    }

    /**
     * Determines if the given exception is an Validation error.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isValidationException(Exception $e)
    {
        return $e instanceof ValidationException;
    }

    /**
     * Determines if the given exception is an Validation error.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isDatabaseException(Exception $e)
    {
        return $e instanceof QueryException;
    }

    public static function getMappedMessages()
    {
        return [
            'dynamic_field_product_dynamic_field_id_foreign' => 'Existem produtos atrelados a este campo dinâmico.',
            'broker_broker_segment_broker_segment_id_foreign' => 'Existem corretoras atreladas a este segmento.',
            'dynamic_field_offer_dynamic_field_option_id_foreign' => 'Esta opção está sendo utilizada em um anúncio.',
            'agents_user_id_foreign' => 'Este usuário está associado a um corretor.',
            'users_email_unique' => 'Este e-mail já foi utilizado, cadastre outro e-mail.',
            'proposals_offer_id_foreign' => 'Existe uma proposta relacionada a este anúncio.',
            'offers_product_id_foreign' => 'Existe um anúncio relacionado a este produto.',
            'messages_user_id_foreign' => 'Existem mensagens associadas a este usuário.',
            'proposals_user_id_foreign' => 'Existem propostas associadas a este usuário.',
        ];
    }

}