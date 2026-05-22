<?php

namespace JamieWood\DataMasking\Concerns;

use JamieWood\DataMasking\DataMaskingService;

trait HasMaskedAttributes
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = parent::toArray();

        /** @var DataMaskingService $service */
        $service = app(DataMaskingService::class);

        return $service->maskArray($data, $this);
    }
}
