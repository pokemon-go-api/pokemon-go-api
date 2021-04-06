# Pokemon GO Pokedex API

This project shows the latest Pokemon GO Pokemon Data as a JSON API.
As source the GameMaster Files from PokeMasters are used.

For the current Raidbosses multiple sources are available.
The current Source can be found in the bottom of the Raid Info Graphics. 

## Examples

### Current Raidlist

**German version**

![Current Raidlist - German](https://pokemon-go-lingen.github.io/pokemon-go-api/api/graphics/German/raidlist.png)

**English version**

![Current Raidlist - German](https://pokemon-go-lingen.github.io/pokemon-go-api/api/graphics/English/raidlist.png)
*Alternative Version*  
![Current Raidlist - German](https://pokemon-go-lingen.github.io/pokemon-go-api/api/graphics/English/raidlist_b.png)

## How to use
The latest resources are available on the Github page as an JSON API with an OpenAPI Documentation.

Visit https://pokemon-go-lingen.github.io/pokemon-go-api/ for the public available API hosted as Github Page.

### Use as local version
If you want to host the API by your own you can download this Project and run the following commands to Update the files.
```bash
composer install
composer run-script api-build
# create a PNG file from the SVG Image
# set the window size for the chromium instance
WINDOW_SIZE=985,992 composer run-script convert-svg
```

## Disclaimer
This repo is for educational use only. All available information found within this repo is the property of The Pokemon Company and Niantic. All copyright belongs to the respective companies. Please respect the original source material.

## Source
This repository uses the latest Game Master files for Pokemon GO from https://github.com/PokeMiners/
Any resources linked from the API are linked directly to the assets repository from the PokeMiners https://github.com/PokeMiners/pogo_assets
