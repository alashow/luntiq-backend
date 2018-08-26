<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::group(['prefix' => 'api'], function () {
        Route::group(['prefix' => 'movies'], function () {
            Route::get('', 'Api\MoviesController@movies')->name('movies');
            Route::patch('{movie}', 'Api\MoviesController@update')->name('movie.update');
            Route::post('toggleAll', 'Api\MoviesController@toggleAll')->name('movie.toggleAll');
        });

        Route::group(['prefix' => 'shows'], function () {
            Route::get('', 'Api\ShowsController@shows')->name('shows');
            Route::get('clearAll', 'Api\ShowsController@clearAll')->name('shows.clearAll');

            Route::get('{show}', 'Api\ShowsController@show')->name('show');
            Route::patch('{show}', 'Api\ShowsController@update')->name('show.update');
            Route::post('{show}/toggleDownload', 'Api\ShowsController@toggleDownload')->name('show.toggleDownload');
        });

        Route::group(['prefix' => 'episodes'], function () {
            Route::patch('{episode}', 'Api\EpisodesController@update')->name('episode.update');
        });

        Route::group(['prefix' => 'episodes'], function () {
            Route::patch('{episode}', 'Api\EpisodesController@update')->name('episode.update');
        });

        Route::group(['prefix' => 'downloads'], function () {
            Route::get('check/{file}', 'Api\DownloadsController@check')->name('downloads.check');
        });
    });

    Route::get('sync', 'HomeController@sync')->name('sync');

    Route::get('/', function () {
        return redirect(route('home'));
    });
});