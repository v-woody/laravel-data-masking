<?php

namespace VWoody\DataMasking\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use VWoody\DataMasking\DataMaskingService;

abstract class MaskedJsonResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        /** @var DataMaskingService $service */
        $service = app(DataMaskingService::class);

        return $service->maskArray($data, $this->resource);
    }
}
