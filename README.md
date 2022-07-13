![Uppy](https://banners.beyondco.de/Uppy.png?theme=light&packageManager=composer&packageName=create-project+everli%2Fuppy&pattern=architect&style=style_1&description=mobile+apps+distribution+platform&md=1&showWatermark=0&fontSize=125px&images=device-mobile)

## Installation

Clone this repository anywhere on your machine:

```shell script
git clone git@github.com:everli/uppy.git
cd uppy
cp .env.example .env 
```

Optionally you can add these environment variables to the `.env` file in order to avoid conflicts with other docker instances

```dotenv
DOCKER_NGINX_PORT=<ANOTHER_PORT>
DOCKER_MYSQL_PORT=<ANOTHER_PORT>
```

A few other commands and you are ready to go

```shell script
docker-compose up -d

docker-compose run --rm composer install
docker-compose run --rm artisan key:generate
docker-compose run --rm artisan migrate:fresh
```

A development server is now started at [http://localhost:8080](http://localhost:8080)

## Building the frontend

In order to see the dashboard, you need to compile the frontend assets:
```shell script
docker-compose run --rm node npm install

cp tailwind.config.js.example tailwind.config.js

docker-compose run --rm node npm run development # development assets
docker-compose run --rm node npm run production # production assets
```

If you are developing on the frontend, is useful to run a watch command, to automatically compile assets on file changes:
```shell script
docker-compose run --rm node npm run watch
```

To enable access to the admin frontend, you need to create the first admin account, that can be done via an artisan command:
```shell script
docker-compose run --rm artisan user:create
```
And just follow the instructions on the screen. It will create also an external token to enable API access using that user.

## Running unit tests
```shell script
docker-compose run --rm php ./vendor/bin/phpunit
```

## Configuration

### Adding new supported platforms

You can add a new supported platforms by creating a new class inside the `app/Platforms` folder and define the `id` and the expected `mimeTypes` of the package, must extend the `Platform` class.
To actually support the platform, the corresponding classpath should be added in the config file `config/uppy.php`.

### Packages storage disk
By default, Uppy will use the Laravel cloud filesystem (`Amazon s3`) to store icons and packages.
You can change the disk where the packages are stored simply adding the `FILESYSTEM_CLOUD` variable to your `.env` file specifying the disk which is to be used.

```dotenv
FILESYSTEM_CLOUD=local # ex. this will save the packages locally
```

If want also customize the root path used by uppy (which, by default it `uppy/`), you can simply add the env variable `ASSETS_FOLDER` to your `.env` to specify the base path on the disk.

```dotenv
ASSETS_FOLDER=base/path/to/Uppy
```

