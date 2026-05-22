<?php

namespace VWoody\DataMasking\Commands;

use Illuminate\Console\Command;
use VWoody\DataMasking\MaskingRegistry;

class VerifyMaskingCommand extends Command
{
    protected $signature = 'data-masking:verify {model : The fully qualified class name of the model}';

    protected $description = 'Display which fields will be masked on a given model and the masker applied to each.';

    public function handle(MaskingRegistry $registry): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class [{$modelClass}] does not exist.");

            return self::FAILURE;
        }

        $resolved = $registry->resolveWithSources(new $modelClass);

        if (empty($resolved)) {
            $this->warn("No masking rules found for [{$modelClass}].");
            $this->line('Make sure the model uses the HasMaskedAttributes trait and has fields defined via attributes, the MasksFields interface, or config.');

            return self::SUCCESS;
        }

        $this->info("Masking rules for [{$modelClass}]:");
        $this->newLine();

        $rows = [];

        foreach ($resolved as $field => $details) {
            $rows[] = [
                $field,
                get_class($details['masker']),
                $details['source'],
            ];
        }

        $this->table(
            ['Field', 'Masker', 'Source'],
            $rows,
        );

        $this->newLine();

        $count = count($rows);
        $this->comment("Total: {$count} field(s) will be masked.");

        return self::SUCCESS;
    }
}
