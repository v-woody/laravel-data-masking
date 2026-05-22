<?php

namespace VWoody\DataMasking\Log;

use Illuminate\Log\Logger;

class MaskingTap
{
    public function __invoke(Logger $logger): void
    {
        /** @var array<string, string> $fieldMaskers */
        $fieldMaskers = config('data-masking.log_fields', []);

        if (empty($fieldMaskers)) {
            return;
        }

        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(new MaskingProcessor($fieldMaskers));
        }
    }
}
