<?php

namespace Mahdianmanesh\LaravelTestGenerator;

use Illuminate\Console\Command;

class TestGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-test:generate
                            {--filter= : Filter to a specific route prefix, such as /api or /v2/api}
                            {--dir= : Directory to which the test file are to be stored within the feature folder}
                            {--sync= : Whether @depends attribute to be added to each function inside the test file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generates unit test cases for this application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $generator = new Generator(
            $this->option('dir') ?: '',
            (bool) $this->option('sync'),
            $this->option('filter')
        );

        $generator->generate();
    }
}
