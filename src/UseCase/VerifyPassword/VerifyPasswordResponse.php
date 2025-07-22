<?php

namespace App\UseCase\VerifyPassword;

use Exception;

class VerifyPasswordResponse
{
    public ?int $strength;
    public ?bool $isOk;
    public ?bool $isLeaked= false;

    public ?Exception $exception = null;


}