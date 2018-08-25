# luntiq-backend

This app generates a movie and tv shows library by scanning Premiumize Cloud files.
It recursively scans your Premiumize Cloud for video files, [guesses](https://guessit.readthedocs.io/en/latest/) it's name, searches in [TMDB](https://themoviedb.com), and adds to the database.

Includes downloader that downloads checked media to local storage via `aria2`.

I created this mainly for myself. A lot of things needs to be documented. Feel free to open an issue or contact me about details.

# Setup

```bash
# copy .env, setup database, add your Premiumize account and TMDB API Key in .env file
cp -n .env.example .env

# install dependencies via https://getcomposer.org/
composer install

# create your database before running this, default database name in .env
# creates tables
php artisan migrate

# main sync library command
# scans for new, removed, changed files in Premiumize Cloud
php artisan sync:library

# start web server for library
# Database dump at http://127.0.0.1:8000/api/library
php artisan serve  
```

# External programs

1. `guessit` command when scanning PM Cloud files to find media files. Install it via `pip install guessit`. [Docs](https://guessit.readthedocs.io/en/latest/)

2. `aria2`s rpc interface for downloading medias. Only when downloads enabled in `.env`. Configure rpc interface location and rpc secret in `.env`. [Docs](https://aria2.github.io/manual/en/html/aria2c.html#rpc-interface).   

