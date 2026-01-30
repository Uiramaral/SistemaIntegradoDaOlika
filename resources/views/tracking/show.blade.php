<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rastreamento - Pedido #{{ $order->order_number }}</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f5f5f5; }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 20px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .status-badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            padding: 8px 16px; 
            border-radius: 20px; 
            font-size: 13px; 
            font-weight: 600; 
            margin-top: 12px;
        }
        .status-badge.active { background: rgba(34, 197, 94, 0.2); color: #16a34a; }
        .status-badge.inactive { background: rgba(148, 163, 184, 0.2); color: #64748b; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        #map { width: 100%; height: calc(100vh - 180px); }
        .info-panel { 
            position: absolute; 
            bottom: 20px; 
            left: 20px; 
            right: 20px; 
            background: white; 
            padding: 16px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
            z-index: 1000;
        }
        .info-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-icon { width: 18px; height: 18px; opacity: 0.6; }
        .loading { text-align: center; padding: 40px; color: #64748b; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .loading-dot { animation: pulse 1.5s infinite; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Pedido #{{ $order->order_number }}</h1>
        <p>{{ $order->customer->name ?? 'Cliente' }}</p>
        <span class="status-badge {{ $order->tracking_enabled ? 'active' : 'inactive' }}" id="status-badge">
            <span class="status-dot"></span>
            <span id="status-text">{{ $order->tracking_enabled ? 'Rastreamento Ativo' : 'Rastreamento Desativado' }}</span>
        </span>
    </div>

    <div id="map"></div>

    <div class="info-panel">
        <div class="info-row">
            <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <strong>Destino:</strong> <span id="destination-text">{{ $order->delivery_address }}</span>
        </div>
        <div class="info-row loading-dot">
            <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span id="update-status">Atualizando localização...</span>
        </div>
    </div>

    <script>
        // Obter coordenadas do endereço de entrega (simulado)
        // Em produção, usar geocoding real (Google Maps Geocoding API, Nominatim, etc)
        const deliveryAddress = "{{ addslashes($order->delivery_address ?? '') }}";
        
        // Coordenadas simuladas para demonstração
        // TODO: Integrar com serviço de geocoding real
        const simulatedDestination = {
            lat: -23.550520, // Coordenadas de exemplo - substituir por geocoding real
            lng: -46.633308
        };
        
        // Centralizar mapa no destino inicialmente
        const map = L.map('map', { zoomControl: true }).setView([simulatedDestination.lat, simulatedDestination.lng], 14);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        let deliveryMarker = null;
        let destinationMarker = null;
        let routeLine = null;
        let firstUpdate = true;

        const deliveryIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const destIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        async function updateLocation() {
            try {
                const response = await fetch('/tracking/{{ $token }}/location');
                const data = await response.json();

                document.getElementById('update-status').textContent = 'Última atualização: ' + new Date().toLocaleTimeString('pt-BR');

                if (!data.tracking_enabled) {
                    document.getElementById('status-badge').className = 'status-badge inactive';
                    document.getElementById('status-text').textContent = 'Rastreamento Desativado';
                }

                // Sempre mostrar o destino (mesmo sem localização do entregador)
                if (!destinationMarker) {
                    destinationMarker = L.marker([simulatedDestination.lat, simulatedDestination.lng], {icon: destIcon})
                        .bindPopup(`<b>📍 Destino da Entrega</b><br>${deliveryAddress || 'Endereço não informado'}`)
                        .addTo(map);
                }

                if (data.current_location) {
                    const lat = data.current_location.lat;
                    const lng = data.current_location.lng;

                    if (!deliveryMarker) {
                        deliveryMarker = L.marker([lat, lng], {icon: deliveryIcon})
                            .bindPopup('🚗 Entregador')
                            .addTo(map);
                    } else {
                        deliveryMarker.setLatLng([lat, lng]);
                    }

                    // Atualizar rota
                    if (routeLine) {
                        routeLine.setLatLngs([
                            [lat, lng],
                            [simulatedDestination.lat, simulatedDestination.lng]
                        ]);
                    } else {
                        routeLine = L.polyline([
                            [lat, lng],
                            [simulatedDestination.lat, simulatedDestination.lng]
                        ], {color: '#667eea', weight: 3, opacity: 0.6, dashArray: '10, 10'}).addTo(map);
                    }

                    // Na primeira atualização, ajustar zoom para mostrar ambos
                    if (firstUpdate) {
                        map.fitBounds([
                            [lat, lng],
                            [simulatedDestination.lat, simulatedDestination.lng]
                        ], {padding: [50, 50]});
                        firstUpdate = false;
                    }
                } else if (firstUpdate) {
                    // Se não tem localização ainda, mostrar apenas o destino
                    map.setView([simulatedDestination.lat, simulatedDestination.lng], 14);
                    firstUpdate = false;
                }
            } catch (error) {
                console.error('Erro ao atualizar localização:', error);
                document.getElementById('update-status').textContent = 'Erro ao atualizar';
            }
        }

        updateLocation();
        setInterval(updateLocation, 5000);
    </script>
</body>
</html>
