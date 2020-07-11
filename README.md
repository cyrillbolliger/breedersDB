# Breeders Database
Stores your plants data and facilitates evaluation. Enables data based plant breeding.

## Dev guide
### Installation
1. Install [docker](https://store.docker.com/search?offering=community&type=edition) and [docker-compose](https://docs.docker.com/compose/install/).
1. Clone this repo `git clone https://github.com/cyrillbolliger/breedersDB`
1. `cd` into the folder containing the repo
1. Execute `bash install.sh` and have a ☕️ while it installs.
1. Visit [localhost:8000/](http://localhost:8000/)

todo: database setup

### Printer Setup
Suggested printer: Zebra P4T
* Driver [download](https://www.zebra.com/us/en/support-downloads/printers/mobile/p4t.html)
* Installation: Use the ZPL driver (not CPCL).
* Configuration: Enable Passthrou mode: Settings > Advanced Settings > Other > Passthrou.
Use `${` and `}$` as delimiters.
