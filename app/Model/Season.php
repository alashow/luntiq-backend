<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $table = 'seasons';

    protected $guarded = ['id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withEpisodes', function (Builder $builder) {
            $builder->has('episodes', '>=', 1);
        });
    }

    /**
     * @param array $seasonResult season object from search result.
     * @param array $showResult   show object from show result.
     *
     * @return array season model fields from given season result and show result.
     */
    public static function build(array $seasonResult, array $showResult)
    {
        $season = [];

        $season['tmdb_id'] = $seasonResult['id'];
        $season['show_id'] = $showResult['id'];
        $season['season_number'] = $seasonResult['season_number'];
        $season['episode_count'] = $seasonResult['episode_count'];
        $season['name'] = $seasonResult['name'];
        $season['overview'] = $seasonResult['overview'];
        $season['poster_path'] = $seasonResult['poster_path'];
        $season['air_date'] = Carbon::parse($seasonResult['air_date']);

        return $season;
    }

    /**
     * Queries linked episodes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class, 'season_id', 'tmdb_id');
    }
}
