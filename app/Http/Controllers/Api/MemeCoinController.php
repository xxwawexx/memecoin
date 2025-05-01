<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateMemeCoinRequest;
use App\Services\MemeCoinService;
use Illuminate\Http\JsonResponse;

class MemeCoinController extends Controller
{
    protected MemeCoinService $service;

    public function __construct(MemeCoinService $service)
    {
        $this->service = $service;
    }

    public function generate(GenerateMemeCoinRequest $request): JsonResponse
    {
        return $this->service->generate($request->user(), $request->input('full_name'));
    }
}
