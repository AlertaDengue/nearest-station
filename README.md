# Nearest Station
The project aim to identify the nearest meteorological station of each municipality of Brazil. It can be used to choose the best data source in researches that depends on brazilian weather data. 

## Data
To achive this goal, we built a list of all meteorological station from the [query form](http://bancodedados.cptec.inpe.br/tabelaestacoes/faces/consultapais.jsp) available on the [CPTEC/INPE](http://bancodedados.cptec.inpe.br/) website.

Moreover, we have used two sources of geojsons to visualize data and as input to distance algorithms:
 * Brazil geojson was taken from [here](http://www.jrossetto.com.br/json/brazil_geo.zip).
 * States and municipalities geojsons was generated using the [br-atlas tool](https://github.com/carolinabigonha/br-atlas).
