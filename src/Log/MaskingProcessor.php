<?php

namespace VWoody\DataMasking\Log;

use VWoody\DataMasking\Contracts\Masker;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class MaskingProcessor implements ProcessorInterface
{
    /**
     * @param array<string, string> $fieldMaskers  field name => masker class
     */
    public function __construct(
        private readonly array $fieldMaskers,
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->maskFields($record->context);
        $extra = $this->maskFields($record->extra);

        return $record->with(context: $context, extra: $extra);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function maskFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskFields($value);

                continue;
            }

            if (! array_key_exists($key, $this->fieldMaskers)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            /** @var Masker $masker */
            $masker = app($this->fieldMaskers[$key]);

            $data[$key] = $masker->mask((string) $value);
        }

        return $data;
    }
}
