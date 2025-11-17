<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- leaflet css link  -->
    <link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    />

    <title>Web-GIS with Geoserver and Leaflet</title>

    <style>
      body {
        margin: 0;
        padding: 0;
      }
      #map {
        width: 100%;
        height: 100vh;
      }
      #map {
        width: 100%;
        height: 100vh;
      }
      .legend-container {
        background-color: rgba(255, 255, 255, 0.8);
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
      }
      .legend-toggle {
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 36px;
        height: 36px;
        line-height: 36px;
        text-align: center;
        font-size: 1.5em;
        float: left;
        cursor: pointer;
      }
      .legend-toggle:hover {
        background-color: #f4f4f4;
      }
      .legend-panel {
        display: none;
        padding: 10px;
        background-color: white;
        border-radius: 0 5px 5px 5px;
        margin-top: 5px;
        width: 250px;
      }
      .legend-panel.show {
        display: block;
      }
      .legend-panel h4 {
        margin: 5px 0 10px;
      }
      .legend-panel div {
        margin-bottom: 5px;
      }
      .legend {
        position: absolute;
        top: 80px;
        left: 20px;
        z-index: 1000;
      }
      .legend-panel img {
        vertical-align: middle;
        margin-right: 5px;
      }
    </style>
  </head>

  <body>
    <div id="map"></div>

    <!-- leaflet js link  -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
      // ===============================
      // MAP DASAR
      // ===============================
      var map = L.map("map").setView([-7.732521, 110.402376], 11);

      var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);

      // ===============================
      // LAYER WMS DARI GEOSERVER
      // ===============================

      // 1. ADMINISTRASIDESA_AR_25K
      var desa = L.tileLayer.wms(
        "http://localhost:8080/geoserver/pgweb10/wms",
        {
          layers: "pgweb10:ADMINISTRASIDESA_AR_25K",
          format: "image/png",
          transparent: true
        }
      ).addTo(map);

      // 2. JALAN_LN_25K
      var jalan = L.tileLayer.wms(
        "http://localhost:8080/geoserver/pgweb10/wms",
        {
          layers: "pgweb10:JALAN_LN_25K",
          format: "image/png",
          transparent: true
        }
      ).addTo(map);

      // 3. data_kecamatan
      var kecamatan = L.tileLayer.wms(
        "http://localhost:8080/geoserver/pgweb10/wms",
        {
          layers: "pgweb10:data_kecamatan",
          format: "image/png",
          transparent: true
        }
      ).addTo(map);

      var toponimi = L.tileLayer.wms(
        "http://localhost:8080/geoserver/pgweb10/wms",
        {
          layers: "pgweb10:toponimitest",
          format: "image/png",
          transparent: true
        }
      ).addTo(map);

      //geoportal
      var geoportal = L.tileLayer.wms(
        "https://geoportal.slemankab.go.id/geoserver/wms",
        {
          layers: "geonode:kasus_leptosirosis_2025_semester1",
          format: "image/png",
          transparent: true
        }
      ).addTo(map);

      // ===============================
      // LAYER CONTROL
      // ===============================
      var baseLayers = {
        "OpenStreetMap": osm
      };

      var overlayLayers = {
        "Administrasi Desa (AR_25K)": desa,
        "Jalan 25K": jalan,
        "Data Kecamatan": kecamatan,
        "toponimitest": toponimi,
        "Kasus Leptospirosis 2025": geoportal
      };

      L.control.layers(baseLayers, overlayLayers).addTo(map);

      // ===============================
      // LEGEND
      // ===============================
      const layerInfo = {
        "Administrasi Desa (AR_25K)": {
            url: "http://localhost:8080/geoserver/pgweb10/wms",
            layer: "pgweb10:ADMINISTRASIDESA_AR_25K"
        },
        "Jalan 25K": {
            url: "http://localhost:8080/geoserver/pgweb10/wms",
            layer: "pgweb10:JALAN_LN_25K"
        },
        "Data Kecamatan": {
            url: "http://localhost:8080/geoserver/pgweb10/wms",
            layer: "pgweb10:data_kecamatan"
        },
        "toponimitest": {
            url: "http://localhost:8080/geoserver/pgweb10/wms",
            layer: "pgweb10:toponimitest"
        },
        "Kasus Leptospirosis 2025": {
            url: "https://geoportal.slemankab.go.id/geoserver/wms",
            layer: "geonode:kasus_leptosirosis_2025_semester1"
        }
      };

      var legend = L.control({position: 'topleft'});

      legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'legend');
        div.innerHTML = '<div class="legend-container">' +
                            '<div class="legend-toggle" onclick="toggleLegend()"><i class="fa fa-bars"></i></div>' +
                            '<div class="legend-panel">' +
                                '<h4>Legenda</h4>' +
                            '</div>' +
                        '</div>';
        return div;
      };

      legend.addTo(map);

      function toggleLegend() {
        document.querySelector('.legend-panel').classList.toggle('show');
      }
      function getLegendUrl(layerName) {
          const info = layerInfo[layerName];
          if (!info) return null;
          return `${info.url}?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=${info.layer}&STYLE=`;
      }

      map.on('overlayadd', function (eventLayer) {
          var legendDiv = document.querySelector('.legend-panel');
          var legendUrl = getLegendUrl(eventLayer.name);
          if (legendUrl) {
              let legendItem = document.createElement('div');
              legendItem.setAttribute('id', 'legend-' + eventLayer.name.replace(/[^a-zA-Z0-9]/g, '-'));
              legendItem.innerHTML = `<img src="${legendUrl}" alt="legend"> ${eventLayer.name}`;
              legendDiv.appendChild(legendItem);
          }
      });

      map.on('overlayremove', function (eventLayer) {
          var legendItem = document.getElementById('legend-' + eventLayer.name.replace(/[^a-zA-Z0-9]/g, '-'));
          if (legendItem) {
              legendItem.remove();
          }
      });

      // Initially add legends for layers that are on by default
      for (const layerName in overlayLayers) {
        if (map.hasLayer(overlayLayers[layerName])) {
            var legendDiv = document.querySelector('.legend-panel');
            var legendUrl = getLegendUrl(layerName);
            if (legendUrl) {
                let legendItem = document.createElement('div');
                legendItem.setAttribute('id', 'legend-' + layerName.replace(/[^a-zA-Z0-9]/g, '-'));
                legendItem.innerHTML = `<img src="${legendUrl}" alt="legend"> ${layerName}`;
                legendDiv.appendChild(legendItem);
            }
        }
      }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  </body>
</html>