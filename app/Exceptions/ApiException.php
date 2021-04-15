<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class ApiException.
 *
 * Custom Exception that provides a status code and a message.
 * It can be converted to JSON, to be used as an API response.
 *
 */
class ApiException extends Exception implements Arrayable, Jsonable
{
    /**
     * @var int
     */
    const ERROR_CODE_GENERAL = 1000;

    /**
     * HTTP Status Code.
     *
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var string
     */
    protected $description;

    /**
     * ApiException constructor.
     *
     * @param int $status
     * @param string|array $message
     * @param array $errors
     * @param int $code
     * @param null $previous
     */
    public function __construct($status, $message, $errors = [], $code = self::ERROR_CODE_GENERAL, $previous = null)
    {
        $this->status = (int) $status;
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getInstance()
    {
        return request()->url();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Convert the exception instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'error' => [
                'status' => $this->getStatus(),
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
                'instance' => $this->getInstance(),
                'errors' => $this->getErrors(),
            ],
        ];
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
