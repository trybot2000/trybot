<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Response::macro('collectionToHtmlTable', function (\Illuminate\Support\Collection $collection) {
            $html = "<table>";

            foreach ($collection as $k => $v) {
                $html .= "<tr>";
                $html .= "<td><strong>$k</strong></td>";
                if (is_string(array_keys($v->toArray())[0])) {
                    // Create a sub-table for these key/value pairs
                    $sub = "<table>";
                    foreach ($v as $k2 => $v2) {
                        $sub .= "<tr><td>$k2</td><td>$v2</td></tr>";
                    }
                    $sub .= "</table>";
                    $html .= "<td>$sub</td>";
                } else {
                    $html .= "<td>$v</td>";
                }
                $html .= "</tr>";
            }

            return $html;
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
