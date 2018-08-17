# luntiq-backend

This app generates a movie and tv shows library by scanning Premiumize Cloud files.
It recursively scans your Premiumize Cloud for video files, [guesses](https://guessit.readthedocs.io/en/latest/) it's name, searches in [TMDB](https://themoviedb.com), and adds to the database.
# Setup

```bash
# copy .env, setup database, add your Premiumize account and TMDB API Key in .env file
cp -n .env.example .env

# install dependencies via https://getcomposer.org/
composer install

# create your database before running this, default database name in .env
# creates tables
php artisan migrate

# main sync command
# scans for new, removed, changed files in Premiumize Cloud
php artisan sync

# start web server for library
# Database dump at http://127.0.0.1:8000/api/library
php artisan serve  
```