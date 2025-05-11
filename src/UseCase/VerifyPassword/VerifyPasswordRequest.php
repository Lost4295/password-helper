<?php

namespace App\UseCase\VerifyPassword;

use App\Service\PasswordService;

class VerifyPasswordRequest
{
    public string $password;
    public int $expectedStrength = PasswordService::STRENGTH_MEDIUM;

    /**
     * @param string $password
     * @param int|null $expectedStrength
     */
    public function __construct(string $password, ?int $expectedStrength)
    {
        $this->password = $password;
        if ($expectedStrength===null){
            $expectedStrength = PasswordService::STRENGTH_MEDIUM;
        }
        $this->expectedStrength = $expectedStrength;
    }
}