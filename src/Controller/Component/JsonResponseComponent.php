<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Response;

/**
 * JsonResponse component
 */
class JsonResponseComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function respondWithErrorJson(array $errors, int $statusCode): Response
    {
        return $this->response
            ->withStringBody(
                json_encode(
                    ['errors' => $errors],
                    JSON_THROW_ON_ERROR
                )
            )
            ->withStatus($statusCode);
    }
}
