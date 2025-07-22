<?php

namespace App\UseCase\VerifyPassword;

use App\Service\PasswordService;
use Exception;

class VerifyPassword
{
    private VerifyPasswordRequest $request;
    private PasswordService $passwordService;
    /**
     * @param VerifyPasswordRequest $request
     */
    public function __construct(VerifyPasswordRequest $request, PasswordService $passwordService)
    {
        $this->request = $request;
        $this->passwordService =$passwordService;
    }

    public function execute(): VerifyPasswordResponse|array
    {
        $response = new VerifyPasswordResponse();
        try {
            $array = $this->doExecute($this->request);
            $response->strength = $array[0];
            $response->isOk = $array[1];
            $response->isLeaked = $array[2];
        }catch (Exception $e){
            $response->exception =$e;
        }
        return $response;

    }

    private function doExecute(VerifyPasswordRequest $request): array
    {
        $password = $request->password;
        $strength=  $this->passwordService::estimateStrength($password);
        $isVulnerable= $this->passwordService->checkVulnerablePasswords($password);
        $vulnerability = ($isVulnerable || $strength < $request->expectedStrength);
        return [$strength, !$vulnerability, $isVulnerable];
    }
}