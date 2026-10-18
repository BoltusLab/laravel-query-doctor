<?php

namespace BoltusLab\QueryDoctor\Http\Middleware;

use BoltusLab\QueryDoctor\Collectors\QueryCollector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetQueryCollector
{
    public function __construct(private readonly QueryCollector $collector)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->collector->reset();

        return $next($request);
    }
}
