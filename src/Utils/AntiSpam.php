<?php

namespace App\Utils;

class AntiSpam
{
    public function __construct(
        private readonly string $kernelSecret,
        private readonly int $antiSpamMinDelay = 4,
    ) {
    }

    public function generateToken(?int $timestamp = null): string
    {
        $timestamp ??= time();

        return $timestamp.'.'.$this->sign((string) $timestamp);
    }

    public function isTokenValid(?string $token): bool
    {
        $parts = explode('.', (string) $token);
        if (2 !== \count($parts)) {
            return false;
        }

        [$timestamp, $signature] = $parts;
        if (!ctype_digit($timestamp) || !hash_equals($this->sign($timestamp), $signature)) {
            return false;
        }

        $elapsed = time() - (int) $timestamp;

        return $elapsed >= $this->antiSpamMinDelay && $elapsed >= 0;
    }

    protected function sign(string $timestamp): string
    {
        return hash_hmac('sha256', $timestamp, $this->kernelSecret);
    }
}
