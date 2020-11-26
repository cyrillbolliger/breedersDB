# Breeders Database
Stores your plants data and facilitates evaluation. Enables data based plant breeding.

## Dev guide
### Installation
1. Install [docker](https://store.docker.com/search?offering=community&type=edition) and [docker-compose](https://docs.docker.com/compose/install/).
1. Clone this repo `git clone https://github.com/cyrillbolliger/breedersDB`
1. `cd` into the folder containing the repo
1. Execute `bash install.sh` and have a ☕️ while it installs.
1. Visit [localhost:8000/](http://localhost:8000/)

### Database
1. Runing migrations: `docker-compose run app bin/cake migrations migrate  --no-lock` (do not use lock file as we can't use the diff function anyhow (Phinx does not support views)).
1. Run the seeder: TODO: implement seeder.

### Printer Setup
Suggested printer: Zebra P4T
* Driver [download](https://www.zebra.com/us/en/support-downloads/printers/mobile/p4t.html)
* Installation: Use the ZPL driver (not CPCL).
* Configuration: Enable Passthrou mode: Settings > Advanced Settings > Other > Passthrou.
Use `${` and `}$` as delimiters.

#### Trouble shooting
* Remove printer (in new **and old** windows settings).
* Add it again using the Zebra Setup Utility.
* Reconfigure Passthrou mode.
