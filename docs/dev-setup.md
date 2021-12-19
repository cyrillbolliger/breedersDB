# Dev guide

## Installation
1. Install [docker](https://store.docker.com/search?offering=community&type=edition) and [docker-compose](https://docs.docker.com/compose/install/).
1. Clone this repo `git clone https://github.com/cyrillbolliger/breedersDB`
1. `cd` into the folder containing the repo
1. Execute `bash install.sh` and have a ☕️ while it installs.
1. Visit [localhost:8000/](http://localhost:8000/)

## Database
1. Running migrations: `docker-compose run app bin/cake migrations migrate  --no-lock` (do not use lock file as we can't use the diff function anyhow (Phinx does not support views)).
1. Running migrations on the test database: `docker-compose run app bin/cake migrations migrate  --no-lock --connection=test`
1. Run the seeder: `docker-compose run app bin/cake migrations seed --seed DemoSeed`
