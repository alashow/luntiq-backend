<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Show extends Model
{
    protected $table = 'shows';

    protected $guarded = ['id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withSeasons', function (Builder $builder) {
            $builder->has('seasons', '>=', 1);
        });
    }

    /**
     * @param array $showResult show object from search result
     *
     * @return array new build show fields from given show result
     */
    public static function build(array $showResult)
    {
        $show = [];

        $show['tmdb_id'] = $showResult['id'];
        $show['name'] = $showResult['name'];
        $show['overview'] = $showResult['overview'];
        $show['homepage'] = $showResult['homepage'];
        $show['genres'] = join(',', array_pluck($showResult['genres'], 'id'));
        $show['languages'] = join(',', $showResult['languages']);
        $show['episode_run_time'] = max($showResult['episode_run_time']);
        $show['popularity'] = $showResult['popularity'];
        $show['vote_average'] = $showResult['vote_average'];
        $show['episode_count'] = $showResult['number_of_episodes'];
        $show['season_count'] = $showResult['number_of_seasons'];
        $show['poster_path'] = $showResult['poster_path'];
        $show['backdrop_path'] = $showResult['backdrop_path'];
        $show['first_air_date'] = Carbon::parse($showResult['first_air_date']);
        $show['last_air_date'] = Carbon::parse($showResult['last_air_date']);

        return $show;
    }

    /**
     * Check if
     *
     * @param $showResult
     *
     * @return bool
     */
    public static function exists($showResult)
    {
        return Show::where('tmdb_id', '=', $showResult['id'])->first() != null ? true : false;
    }

    /**
     * Queries linked seasons.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seasons()
    {
        return $this->hasMany(Season::class, 'show_id', 'tmdb_id');
    }
}
