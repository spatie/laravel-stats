<?php

namespace Spatie\Stats\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;

class StatsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new stats class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Stats';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() : string
    {
        return __DIR__ . '/stubs/stats_class.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace The root namespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace) : string
    {
        return $rootNamespace . '\Stats';
    }

    /**
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name): string
    {
        if (!$this->argument('name')) {
            throw new InvalidArgumentException("Missing required argument model name");
        } else {
            $name = $this->argument('name');
            $stub = str_replace('DummyClass', $name, $stub);
            $stub = str_replace('DummySlug', Str::slug(Str::headline($name)), $stub);
        }
        return $stub;
    }
}
