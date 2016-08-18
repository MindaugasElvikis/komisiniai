<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommissionCommandTest extends TestCase
{

    /**
     * @test Command returns correct calculations.
     */
    public function testIfOperationsAreCalculatedCorrectly()
    {
        $this->createTempFileAndTest("2016-01-05,1,natural,cash_in,200.00,EUR", "0.06");
        $this->createTempFileAndTest("2016-01-10,2,juridical,cash_in,1000000.00,EUR", "5.00");
        $this->createTempFileAndTest("2016-01-06,1,natural,cash_out,30000,JPY", "0");
    }

    /**
     * @test Command returns error if provided file is not found.
     */
    public function testItErrorsIfFileNotFound()
    {
        // Run calculate:commissions test_file.csv
        // Receive "The provided file was not found!"
        $this->checkCommand('calculate:commissions', 'test_file.csv', 'The provided file was not found!');
    }

    /**
     * Creates file with specified content, calls functions to test that file and check output.
     *
     * @param string $content
     * @param string $expectation
     */
    private function createTempFileAndTest($content, $expectation)
    {
        Storage::put('test.csv', $content);
        $filePath = storage_path('app/test.csv');
        $this->checkCommand('calculate:commissions', $filePath, $expectation);
        Storage::delete('test.csv');
    }

    /**
     * Will check if command has some text.
     *
     * @param string $command
     * @param string $file
     * @param string $messageToSearch
     */
    private function checkCommand($command, $file, $messageToSearch)
    {
        $kernel = $this->app->make(Illuminate\Contracts\Console\Kernel::class);
        $status = $kernel->handle(
            $input = new Symfony\Component\Console\Input\ArrayInput([
                'command' => $command,
                'file' => $file,
            ]),
            $output = new Symfony\Component\Console\Output\BufferedOutput
        );

        $this->assertContains($messageToSearch, str_replace("\n", "", $output->fetch()));
    }
}
