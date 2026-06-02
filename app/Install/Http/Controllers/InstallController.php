<?php

namespace App\Install\Http\Controllers;

use App\Install\Steps\ConfigureDatabase;
use App\Install\Steps\CreateAdminUser;
use App\Install\Steps\EnableBundledExtensions;
use App\Install\Steps\FinishInstall;
use App\Install\Steps\SeedDefaultSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class InstallController
{
    public function index(Request $request)
    {
        return view('pages::install.index');
    }

    public function store(Request $request)
    {
        //        dd($request->all());

        try {
            Pipeline::send($request->all())
                ->through([
                    ConfigureDatabase::class,
                    SeedDefaultSettings::class,
                    CreateAdminUser::class,
                    //                    EnableBundledExtensions::class,
                    FinishInstall::class,
                ])
                ->thenReturn();
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }

        return redirect()->route('install.index');
    }
}
