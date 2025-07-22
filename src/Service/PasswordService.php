<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PasswordService
{
    public const STRENGTH_VERY_WEAK = 0;
    public const STRENGTH_WEAK = 1;
    public const STRENGTH_MEDIUM = 2;
    public const STRENGTH_STRONG = 3;
    public const STRENGTH_VERY_STRONG = 4;


    public static function estimateStrength(#[\SensitiveParameter] string $password): int
    {
        if (!$length = \strlen($password)) {
            return self::STRENGTH_VERY_WEAK;
        }
        $password = count_chars($password, 1);
        $chars = \count($password);

        $control = $digit = $upper = $lower = $symbol = $other = 0;
        foreach ($password as $chr => $count) {
            match (true) {
                $chr < 32 || 127 === $chr => $control = 33,
                48 <= $chr && $chr <= 57 => $digit = 10,
                65 <= $chr && $chr <= 90 => $upper = 26,
                97 <= $chr && $chr <= 122 => $lower = 26,
                128 <= $chr => $other = 128,
                default => $symbol = 33,
            };
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;
        $entropy = $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);

        return match (true) {
            $entropy >= 120 => self::STRENGTH_VERY_STRONG,
            $entropy >= 100 => self::STRENGTH_STRONG,
            $entropy >= 80 => self::STRENGTH_MEDIUM,
            $entropy >= 60 => self::STRENGTH_WEAK,
            default => self::STRENGTH_VERY_WEAK,
        };
    }

    public static function getStrengthName(?int $strength)
    {
        return match ($strength) {
            self::STRENGTH_VERY_WEAK => "Very Weak",
            self::STRENGTH_WEAK => "Weak",
            self::STRENGTH_MEDIUM => "Medium",
            self::STRENGTH_STRONG => "Strong",
            self::STRENGTH_VERY_STRONG => "Very Strong",
            default => "Unknown Strength"
        };
    }

    public function checkVulnerablePasswords(string $password): bool
    {
        $client = HttpClient::create();
        $url = "https://raw.githubusercontent.com/danielmiessler/SecLists/refs/heads/master/Passwords/Common-Credentials/xato-net-10-million-passwords.txt";
        $isVulnerable = $this->checkIfPasswordIsVulnerableAsStream($client, $password, $url);
        if (!$isVulnerable) {
            $url = "https://github.com/danielmiessler/SecLists/raw/refs/heads/master/Passwords/Leaked-Databases/alleged-gmail-passwords.txt";
            $isVulnerable = $this->checkIfPasswordIsVulnerableAsStream($client, $password, $url);
        }
        if (!$isVulnerable) {
            $url = "https://raw.githubusercontent.com/danielmiessler/SecLists/refs/heads/master/Passwords/Common-Credentials/Language-Specific/French-common-password-list-top-20000.txt";
            $isVulnerable = $this->checkIfPasswordIsVulnerableAsStream($client, $password, $url);
        }
        return $isVulnerable;
    }

    /**
     * @param HttpClientInterface $client
     * @param string $password
     * @param string $url
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function checkIfPasswordIsVulnerableAsStream(HttpClientInterface $client, string $password, string $url): bool
    {
        $response = $client->request(
            'GET',
            $url);
        $found = false;
        foreach ($client->stream($response) as $chunk) {
            $chunkArray = preg_split("/[\s,]+/", $chunk->getContent());
            foreach ($chunkArray as $element) {
                if ($element === $password) {
                    $response->cancel();
                    $found = true;
                    break;
                }
            }
            if ($found) {
                break;
            }

        }
        return $found;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function checkIfPasswordIsVulnerable(HttpClientInterface $client, string $password, string $url): bool
    {
        $response = $client->request(
            'GET',
            $url);
        $found = false;
        foreach ($response->toArray() as $element) {
            if ($element === $password) {
                $response->cancel();
                $found = true;
                break;
            }
        }
        return $found;
    }
}