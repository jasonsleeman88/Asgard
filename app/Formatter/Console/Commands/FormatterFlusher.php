<?php

namespace App\Formatter\Console\Commands;

use App\Formatter\Formatter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

class FormatterFlusher extends Command
{
    protected $signature = 'formatter:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(Formatter $formatter)
    {
        \Laravel\Prompts\info('Flushing formatter cache');

        $formatter->flush();

        \Laravel\Prompts\info('Formatter cache flushed');
    }
}
