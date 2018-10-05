<?php

namespace App\Listeners;

use Log;
use Exception;
use Tmdb\Client;
use App\Model\Show;
use App\Model\Movie;
use App\Model\Season;
use App\Model\Episode;
use App\Model\PremFile;
use App\Events\FilesAddedEvent;

class NewFilesListener
{
    /**
     * @var Client
     */
    protected $tmdbClient;

    /**
     * Cache of seasons with episodes.
     *
     * @var array
     */
    protected $seasonsCache;

    /**
     * NewFilesListener constructor.
     */
    public function __construct()
    {
        $this->tmdbClient = resolve('TmdbClient');
    }

    /**
     * Handle the event.
     *
     * @param  FilesAddedEvent $event
     *
     * @return void
     */
    public function handle($event)
    {
        $premFiles = PremFile::videos()->whereIn('prem_id', $event->newFilesIds)->get();

        foreach ($premFiles as $premFile) {
            $guessed = $premFile->guessIt();
            Log::info('Guessed a file', [$guessed]);

            if ($guessed == null) {
                Log::warning("Couldn't guess the file", [$premFile]);
                continue;
            }

            $scanned = false;
            switch ($guessed->type) {
                case 'movie':
                    $scanned = $this->handleMovie($premFile, $guessed);
                    break;
                case 'episode':
                    $scanned = $this->handleEpisode($premFile, $guessed);
                    break;
                default:
                    Log::warning('Guessed files type was unknown', [$guessed, $premFile]);
            }

            $premFile->setScanned($scanned);
        }
    }

    /**
     * Handles guessed movie.
     *
     * @param PremFile  $premFile
     * @param \stdClass $guessed
     *
     * @return bool successfully scanned if true
     */
    private function handleMovie(PremFile $premFile, \stdClass $guessed)
    {
        $params = [];
        if (isset($guessed->year)) {
            $params['year'] = $guessed->year;
        }

        $results = collect($this->tmdbClient->getSearchApi()->searchMovies($guessed->title, $params)['results']);
        if ($results->isNotEmpty()) {
            $movieResult = $results->first();
            $movie = Movie::build($movieResult, $premFile, $guessed);
            $saved = $movie->safeSave();

            if ($saved) Log::info("Successfully added a movie to the database", [$movie]);
            return $saved;
        } else {
            Log::warning('TMDB returned empty result for guessed movie file', [
                $premFile, $guessed,
            ]);
        }
        return false;
    }

    /**
     * Handles guessed episode. Build show and seasons library beofore adding the episode.
     *
     * @param PremFile  $premFile
     * @param \stdClass $guessed
     *
     * @return bool successfully scanned if true
     */
    private function handleEpisode(PremFile $premFile, \stdClass $guessed)
    {
        if (! isset($guessed->episode)) {
            Log::warning("Skipping an episode without an episode number. Probably a special episode or file name doesn't have episode number in it.");
            return false;
        }

        // choose first episode number if it's two episode file
        if (is_array($guessed->episode)) {
            $guessed->episode = array_first($guessed->episode);
        }

        Log::info('Searching show for a file: '.$premFile->name);
        $results = collect($this->tmdbClient->getSearchApi()->searchTv($guessed->title)['results']);
        if ($results->isNotEmpty()) {
            $firstShowResult = $results->first();
            if (! Show::exists($firstShowResult)) {
                Log::info("Building a show..", $firstShowResult);

                $showResult = $this->tmdbClient->getTvApi()->getTvshow($firstShowResult['id']);

                $show = Show::build($showResult);
                Show::insert($show);

                foreach ($showResult['seasons'] as $seasonResult) {
                    if (! Season::exists($seasonResult)) {
                        $season = Season::build($seasonResult, $showResult);
                        Season::insert($season);
                    }
                }
            } else {
                Log::info('Show already exists in database, checking season.', [$firstShowResult['name']]);

                $season = $guessed->season;
                $seasonResult = $this->tmdbClient->getTvSeasonApi()->getSeason($firstShowResult['id'], $season);
                if (! Season::exists($seasonResult)) {
                    $season = Season::build($seasonResult, $firstShowResult);
                    Season::insert($season);
                } else {
                    Log::info("Season $season already exists in database, continuing", [$seasonResult['name']]);
                }
            }

            $seasonResult = $this->getSeasonFromCache($firstShowResult, $guessed);
            if ($seasonResult == null) {
                $seasonResult = $this->tmdbClient->getTvSeasonApi()->getSeason($firstShowResult['id'], $guessed->season);
                $this->putSeasonToCache($seasonResult, $firstShowResult, $guessed);
            }

            $episodeResult = $this->getEpisode($seasonResult, $guessed);
            // try getting from episode api if it's not in season result
            if ($episodeResult == null) {
                $episodeResult = $this->tmdbClient->getTvEpisodeApi()->getEpisode($firstShowResult['id'], $guessed->season, $guessed->episode);
            }
            if ($episodeResult != null) {
                $episode = Episode::build($episodeResult, $seasonResult, $firstShowResult, $premFile);
                $saved = $episode->safeSave();

                if ($saved) Log::info("Successfully added an episode to the database", [$episode]);
                return $saved;
            } else {
                Log::warning("Couldn't find episode from TMDB season result", [
                    $premFile, $guessed,
                ]);
            }
        } else {
            Log::warning('TMDB returned empty result for guessed episode file', [
                $premFile, $guessed,
            ]);
        }
        return false;
    }

    /**
     * Get season from memory cache.
     *
     * @param array     $showResult
     * @param \stdClass $guessed
     *
     * @return array|null
     */
    private function getSeasonFromCache(array $showResult, \stdClass $guessed)
    {
        try {
            return $this->seasonsCache[$showResult['id']][$guessed->season];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Put season in memory cache.
     *
     * @param array     $seasonResult
     * @param array     $showResult
     * @param \stdClass $guessed
     */
    private function putSeasonToCache(array $seasonResult, array $showResult, \stdClass $guessed)
    {
        $this->seasonsCache[$showResult['id']][$guessed->season] = $seasonResult;
    }

    /**
     * Get episode from season memory cache.
     *
     * @param array     $seasonResult
     * @param \stdClass $guessed
     *
     * @return array|null
     */
    private function getEpisode(array $seasonResult, $guessed)
    {
        foreach ($seasonResult['episodes'] as $episode) {
            if ($episode['episode_number'] == $guessed->episode) {
                return $episode;
            }
        }
        return null;
    }
}
