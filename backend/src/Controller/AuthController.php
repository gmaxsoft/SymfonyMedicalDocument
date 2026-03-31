<?php

declare(strict_types=1);

namespace App\Controller;

use LogicException;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController
{
    /**
     * Route target for JSON login — the security firewall intercepts valid credentials and returns a JWT.
     */
    #[Route('/api/auth', name: 'api_auth', methods: ['POST'])]
    public function __invoke(): never
    {
        throw new LogicException('POST /api/auth must be handled by json_login (Lexik JWT success handler).');
    }
}
