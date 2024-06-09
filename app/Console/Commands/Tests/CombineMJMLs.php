<?php

namespace App\Console\Commands\Tests;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class CombineMJMLs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:combine-mjmls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will help combine mjmls and save them as blade';

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
     * @return int
     */
    public function handle()
    {

        $src = base_path("resources/views/mails/mjmls");
        $dst = base_path("resources/views/mails/mjmls_blades");

        if(File::exists($dst)) File::cleanDirectory($dst);

        $includes = collect(File::files( $src ))->filter(fn( SplFileInfo $x ) => Str::of($x->getFilename())->startsWith("_") );
        foreach ( collect(File::files( $src ))->filter(fn( SplFileInfo $x ) => !Str::of($x->getFilename())->startsWith("_") ) as $file )
        {
            $blade = $this->preprocessFile( $file, $dst );
            foreach ($includes as $include)
                $blade = $this->checkForInclude($blade, $include);
        }

        return 0;
    }

    private function preprocessFile(SplFileInfo $file, string $destinationPath): SplFileInfo
    {
        $destinationFilePath  = $destinationPath . "/" . $file->getFilenameWithoutExtension() . ".blade.php";
        File::put( $destinationFilePath, $file->getContents() );

        return new SplFileInfo( $destinationFilePath, $destinationPath, pathinfo( $destinationPath, PATHINFO_DIRNAME ) );
        // $file->getPath() base directory
    }

    private function checkForInclude(SplFileInfo $blade, SplFileInfo $include): SplFileInfo
    {
        $target = sprintf( '<mj-include path="./%s" />', $include->getFilename() );
        if( Str::of($blade->getContents())->contains( $target ) )
            File::put( $blade->getRealPath(),  Str::of($blade->getContents())->replace( $target, $include->getContents() ) );

        return $blade;
    }

}
