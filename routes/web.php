<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index');
    Route::get('/home', 'HomeController@home')->name('home');

    Route::get('sync', 'HomeController@sync')->name('sync');

    Route::group(['prefix' => 'api'], function () {
        Route::get('library', 'LibraryController@index');

        Route::group(['prefix' => 'movies'], function () {
            Route::get('', 'Api\MoviesController@movies')->name('movies');
            Route::post('toggleAll', 'Api\MoviesController@toggleAll')->name('movie.toggleAll');

            Route::get('{movie}', 'Api\MoviesController@show')->name('movie');
            Route::patch('{movie}', 'Api\MoviesController@update')->name('movie.update');
        });

        Route::group(['prefix' => 'shows'], function () {
            Route::get('', 'Api\ShowsController@shows')->name('shows');
            Route::get('clearAll', 'Api\ShowsController@clearAll')->name('shows.clearAll');

            Route::get('{show}', 'Api\ShowsController@show')->name('show');
            Route::patch('{show}', 'Api\ShowsController@update')->name('show.update');
            Route::post('{show}/toggleDownload', 'Api\ShowsController@toggleDownload')->name('show.toggleDownload');
        });

        Route::group(['prefix' => 'seasons'], function () {
            Route::get('{season}', 'Api\SeasonsController@season')->name('season');
            Route::post('{season}/toggleDownload', 'Api\SeasonsController@toggleDownload')->name('season.toggleDownload');
        });

        Route::group(['prefix' => 'episodes'], function () {
            Route::get('{episode}', 'Api\EpisodesController@show')->name('episode');
            Route::patch('{episode}', 'Api\EpisodesController@update')->name('episode.update');
        });

        Route::group(['prefix' => 'episodes'], function () {
            Route::patch('{episode}', 'Api\EpisodesController@update')->name('episode.update');
        });

        Route::group(['prefix' => 'downloads'], function () {
            Route::get('check/{file}', 'Api\DownloadsController@check')->name('downloads.check');
            Route::get('library', 'Api\DownloadsController@library')->name('downloads.library');
            Route::get('shows', 'Api\DownloadsController@shows')->name('downloads.shows');
        });
    });
});