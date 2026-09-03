(function () {
    'use strict';

    var CACHE_KEY = 'weatherData_v2';
    var CACHE_DURATION = 10 * 60 * 1000;
    var SEOUL_LAT = 37.5665;
    var SEOUL_LON = 126.9780;
    var widget;
    var googleApiKey = '';

    var cities = [
        {name: '서울', lat: 37.5665, lon: 126.9780, range: 0.15},
        {name: '부산', lat: 35.1795, lon: 129.0756, range: 0.15},
        {name: '대구', lat: 35.8714, lon: 128.6014, range: 0.15},
        {name: '인천', lat: 37.4563, lon: 126.7052, range: 0.15},
        {name: '광주', lat: 35.1595, lon: 126.8526, range: 0.15},
        {name: '대전', lat: 36.3504, lon: 127.3845, range: 0.15},
        {name: '울산', lat: 35.5383, lon: 129.3113, range: 0.15},
        {name: '세종', lat: 36.4800, lon: 127.2890, range: 0.15},
        {name: '수원', lat: 37.2636, lon: 127.0286, range: 0.10},
        {name: '성남', lat: 37.4449, lon: 127.1388, range: 0.10},
        {name: '용인', lat: 37.2410, lon: 127.1775, range: 0.10},
        {name: '고양', lat: 37.6584, lon: 126.8320, range: 0.10},
        {name: '창원', lat: 35.2281, lon: 128.6811, range: 0.10},
        {name: '청주', lat: 36.6424, lon: 127.4890, range: 0.10},
        {name: '전주', lat: 35.8242, lon: 127.1480, range: 0.10},
        {name: '천안', lat: 36.8151, lon: 127.1139, range: 0.10},
        {name: '제주', lat: 33.4996, lon: 126.5312, range: 0.20}
    ];

    function getCachedWeather() {
        try {
            var cached = localStorage.getItem(CACHE_KEY);
            if (!cached) return null;

            var data = JSON.parse(cached);
            if (Date.now() - data.timestamp < CACHE_DURATION) return data;
        } catch (error) {
            // Storage may be unavailable in private or restricted browsing modes.
        }
        return null;
    }

    function saveWeather(weather, lat, lon, source) {
        try {
            localStorage.setItem(CACHE_KEY, JSON.stringify({
                weather: weather,
                lat: lat,
                lon: lon,
                source: source,
                timestamp: Date.now()
            }));
        } catch (error) {
            // The widget still works when browser storage is unavailable.
        }
    }

    function getLocationName(lat, lon) {
        for (var i = 0; i < cities.length; i++) {
            if (Math.abs(lat - cities[i].lat) < cities[i].range &&
                Math.abs(lon - cities[i].lon) < cities[i].range) {
                return cities[i].name;
            }
        }
        return '현재 위치';
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderWeather(iconClass, temp, location, humidity, windSpeed, description) {
        widget.innerHTML =
            '<div class="weatherContent">' +
                '<div class="weatherMain">' +
                    '<i class="bx ' + escapeHtml(iconClass) + ' weatherIcon" aria-hidden="true"></i>' +
                    '<div class="weatherTemp">' +
                        '<span class="tempValue">' + Math.round(Number(temp) || 0) + '°</span>' +
                        '<span class="tempLocation">' + escapeHtml(location) + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="weatherDetails">' +
                    '<div class="weatherDetail"><i class="bx bx-droplet" aria-hidden="true"></i><span>' +
                        Math.round(Number(humidity) || 0) + '%</span></div>' +
                    '<div class="weatherDetail"><i class="bx bx-wind" aria-hidden="true"></i><span>' +
                        (Number(windSpeed) || 0).toFixed(1) + 'm/s</span></div>' +
                    '<div class="weatherDetail"><span class="weatherDesc">' +
                        escapeHtml(description) + '</span></div>' +
                '</div>' +
            '</div>';
    }

    function displayGreeting() {
        var hour = new Date().getHours();
        var iconClass;
        var greeting;

        if (hour >= 6 && hour < 12) {
            iconClass = 'bx-sun';
            greeting = '좋은 아침입니다';
        } else if (hour >= 12 && hour < 18) {
            iconClass = 'bxs-sun';
            greeting = '좋은 오후입니다';
        } else if (hour >= 18 && hour < 22) {
            iconClass = 'bx-cloud';
            greeting = '좋은 저녁입니다';
        } else {
            iconClass = 'bx-moon';
            greeting = '편안한 밤 되세요';
        }

        widget.innerHTML =
            '<div class="weatherContent"><div class="weatherMain">' +
                '<i class="bx ' + iconClass + ' weatherIcon" aria-hidden="true"></i>' +
                '<div class="weatherTemp"><span class="greetingText">' + greeting + '</span></div>' +
            '</div></div>';
    }

    function isNight() {
        var hour = new Date().getHours();
        return hour < 6 || hour >= 18;
    }

    function getWttrIcon(code) {
        var night = isNight();
        var icons = {
            113: night ? 'bx-moon' : 'bx-sun',
            116: night ? 'bx-cloud' : 'bx-cloud-sun',
            119: 'bx-cloud', 122: 'bx-cloud', 143: 'bx-cloud',
            176: 'bx-cloud-drizzle', 179: 'bx-cloud-snow', 182: 'bx-cloud-snow',
            185: 'bx-cloud-drizzle', 200: 'bx-cloud-lightning',
            227: 'bx-cloud-snow', 230: 'bx-cloud-snow', 248: 'bx-cloud', 260: 'bx-cloud',
            263: 'bx-cloud-drizzle', 266: 'bx-cloud-drizzle', 281: 'bx-cloud-drizzle',
            284: 'bx-cloud-rain', 293: 'bx-cloud-rain', 296: 'bx-cloud-rain',
            299: 'bx-cloud-rain', 302: 'bx-cloud-rain', 305: 'bx-cloud-rain',
            308: 'bx-cloud-rain', 311: 'bx-cloud-rain', 314: 'bx-cloud-rain',
            317: 'bx-cloud-snow', 320: 'bx-cloud-snow', 323: 'bx-cloud-snow',
            326: 'bx-cloud-snow', 329: 'bx-cloud-snow', 332: 'bx-cloud-snow',
            335: 'bx-cloud-snow', 338: 'bx-cloud-snow', 350: 'bx-cloud-snow',
            353: 'bx-cloud-rain', 356: 'bx-cloud-rain', 359: 'bx-cloud-rain',
            362: 'bx-cloud-snow', 365: 'bx-cloud-snow', 368: 'bx-cloud-snow',
            371: 'bx-cloud-snow', 374: 'bx-cloud-snow', 377: 'bx-cloud-snow',
            386: 'bx-cloud-lightning', 389: 'bx-cloud-lightning',
            392: 'bx-cloud-lightning', 395: 'bx-cloud-lightning'
        };
        return icons[code] || (night ? 'bx-moon' : 'bx-sun');
    }

    function getWmoIcon(code) {
        var night = isNight();
        var icons = {
            0: night ? 'bx-moon' : 'bx-sun',
            1: night ? 'bx-moon' : 'bx-sun',
            2: night ? 'bx-cloud' : 'bx-cloud-sun',
            3: 'bx-cloud', 45: 'bx-cloud', 48: 'bx-cloud',
            51: 'bx-cloud-drizzle', 53: 'bx-cloud-drizzle', 55: 'bx-cloud-drizzle',
            56: 'bx-cloud-drizzle', 57: 'bx-cloud-drizzle',
            61: 'bx-cloud-rain', 63: 'bx-cloud-rain', 65: 'bx-cloud-rain',
            66: 'bx-cloud-rain', 67: 'bx-cloud-rain',
            71: 'bx-cloud-snow', 73: 'bx-cloud-snow', 75: 'bx-cloud-snow', 77: 'bx-cloud-snow',
            80: 'bx-cloud-rain', 81: 'bx-cloud-rain', 82: 'bx-cloud-rain',
            85: 'bx-cloud-snow', 86: 'bx-cloud-snow',
            95: 'bx-cloud-lightning', 96: 'bx-cloud-lightning', 99: 'bx-cloud-lightning'
        };
        return icons[code] || (night ? 'bx-moon' : 'bx-sun');
    }

    function getWmoDescription(code) {
        var descriptions = {
            0: '맑음', 1: '대체로 맑음', 2: '구름 조금', 3: '흐림',
            45: '안개', 48: '서리 안개',
            51: '약한 이슬비', 53: '이슬비', 55: '강한 이슬비',
            56: '어는 이슬비', 57: '강한 어는 이슬비',
            61: '약한 비', 63: '비', 65: '강한 비', 66: '어는 비', 67: '강한 어는 비',
            71: '약한 눈', 73: '눈', 75: '강한 눈', 77: '싸라기눈',
            80: '약한 소나기', 81: '소나기', 82: '강한 소나기',
            85: '약한 눈소나기', 86: '강한 눈소나기',
            95: '뇌우', 96: '우박 뇌우', 99: '강한 우박 뇌우'
        };
        return descriptions[code] || '맑음';
    }

    function displayWttr(data, lat, lon) {
        if (!data || !data.current_condition || !data.current_condition[0]) {
            displayGreeting();
            return;
        }

        var current = data.current_condition[0];
        var code = parseInt(current.weatherCode, 10) || 0;
        var description = current.lang_ko && current.lang_ko[0]
            ? current.lang_ko[0].value
            : (current.weatherDesc && current.weatherDesc[0] ? current.weatherDesc[0].value : '맑음');

        renderWeather(
            getWttrIcon(code),
            parseFloat(current.temp_C) || 0,
            getLocationName(lat, lon),
            parseInt(current.humidity, 10) || 0,
            (parseFloat(current.windspeedKmph) || 0) / 3.6,
            description
        );
    }

    function displayOpenMeteo(data, lat, lon) {
        if (!data || !data.current) {
            displayGreeting();
            return;
        }

        var current = data.current;
        var code = Number(current.weather_code) || 0;
        renderWeather(
            getWmoIcon(code),
            Number(current.temperature_2m) || 0,
            getLocationName(lat, lon),
            Number(current.relative_humidity_2m) || 0,
            (Number(current.wind_speed_10m) || 0) / 3.6,
            getWmoDescription(code)
        );
    }

    function getGoogleIcon(code) {
        var night = isNight();
        var icons = {
            CLEAR: night ? 'bx-moon' : 'bx-sun',
            MOSTLY_CLEAR: night ? 'bx-moon' : 'bx-sun',
            PARTLY_CLOUDY: night ? 'bx-cloud' : 'bx-cloud-sun',
            MOSTLY_CLOUDY: 'bx-cloud', CLOUDY: 'bx-cloud', OVERCAST: 'bx-cloud',
            FOG: 'bx-cloud', LIGHT_FOG: 'bx-cloud', DRIZZLE: 'bx-cloud-drizzle',
            LIGHT_RAIN: 'bx-cloud-rain', RAIN: 'bx-cloud-rain', MODERATE_RAIN: 'bx-cloud-rain',
            HEAVY_RAIN: 'bx-cloud-rain', FREEZING_RAIN: 'bx-cloud-rain',
            LIGHT_SNOW: 'bx-cloud-snow', SNOW: 'bx-cloud-snow', MODERATE_SNOW: 'bx-cloud-snow',
            HEAVY_SNOW: 'bx-cloud-snow', SLEET: 'bx-cloud-snow', HAIL: 'bx-cloud-snow',
            THUNDERSTORM: 'bx-cloud-lightning', THUNDERSTORMS_RAIN: 'bx-cloud-lightning'
        };
        return icons[code] || (night ? 'bx-moon' : 'bx-sun');
    }

    function getGoogleDescription(code, fallback) {
        var descriptions = {
            CLEAR: '맑음', MOSTLY_CLEAR: '대체로 맑음', PARTLY_CLOUDY: '구름 조금',
            MOSTLY_CLOUDY: '구름 많음', CLOUDY: '흐림', OVERCAST: '흐림',
            FOG: '안개', LIGHT_FOG: '옅은 안개', DRIZZLE: '이슬비',
            LIGHT_RAIN: '약한 비', RAIN: '비', MODERATE_RAIN: '비', HEAVY_RAIN: '폭우',
            FREEZING_RAIN: '어는 비', LIGHT_SNOW: '약한 눈', SNOW: '눈',
            MODERATE_SNOW: '눈', HEAVY_SNOW: '폭설', SLEET: '진눈깨비', HAIL: '우박',
            THUNDERSTORM: '뇌우', THUNDERSTORMS_RAIN: '뇌우'
        };
        return descriptions[code] || fallback || '맑음';
    }

    function displayGoogle(data, lat, lon) {
        if (!data || !data.temperature) {
            displayGreeting();
            return;
        }

        var code = data.weatherCondition && data.weatherCondition.type
            ? data.weatherCondition.type : 'CLEAR';
        var fallback = data.weatherCondition && data.weatherCondition.description
            ? data.weatherCondition.description.text : '';

        renderWeather(
            getGoogleIcon(code),
            data.temperature.degrees || 0,
            getLocationName(lat, lon),
            data.humidity ? data.humidity.percent : 0,
            data.wind && data.wind.speed ? data.wind.speed.value : 0,
            getGoogleDescription(code, fallback)
        );
    }

    function getGoogleWeather(lat, lon) {
        if (!googleApiKey) {
            displayGreeting();
            return;
        }

        var url = 'https://weather.googleapis.com/v1/currentConditions:lookup?key=' +
            encodeURIComponent(googleApiKey) + '&location.latitude=' + encodeURIComponent(lat) +
            '&location.longitude=' + encodeURIComponent(lon) + '&languageCode=ko';

        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('Google Weather HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                displayGoogle(data, lat, lon);
                saveWeather(data, lat, lon, 'google');
            })
            .catch(displayGreeting);
    }

    function getOpenMeteoWeather(lat, lon) {
        var url = 'https://api.open-meteo.com/v1/forecast?latitude=' + encodeURIComponent(lat) +
            '&longitude=' + encodeURIComponent(lon) +
            '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=auto';

        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('Open-Meteo HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                displayOpenMeteo(data, lat, lon);
                saveWeather(data, lat, lon, 'openmeteo');
            })
            .catch(function () {
                getGoogleWeather(lat, lon);
            });
    }

    function getWeather(lat, lon) {
        var url = 'https://wttr.in/' + encodeURIComponent(lat) + ',' + encodeURIComponent(lon) + '?format=j1';

        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('wttr.in HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                displayWttr(data, lat, lon);
                saveWeather(data, lat, lon, 'wttr');
            })
            .catch(function () {
                getOpenMeteoWeather(lat, lon);
            });
    }

    function displayCached(cached) {
        if (cached.source === 'google') {
            displayGoogle(cached.weather, cached.lat, cached.lon);
        } else if (cached.source === 'openmeteo') {
            displayOpenMeteo(cached.weather, cached.lat, cached.lon);
        } else {
            displayWttr(cached.weather, cached.lat, cached.lon);
        }
    }

    function startWeatherWidget() {
        widget = document.getElementById('weatherWidget');
        if (!widget || typeof window.fetch !== 'function') return;

        googleApiKey = widget.getAttribute('data-google-api-key') || '';
        var cached = getCachedWeather();

        if (!navigator.geolocation) {
            if (cached) displayCached(cached);
            else getWeather(SEOUL_LAT, SEOUL_LON);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;

                if (cached && Math.abs(cached.lat - lat) < 0.1 && Math.abs(cached.lon - lon) < 0.1) {
                    displayCached(cached);
                } else {
                    getWeather(lat, lon);
                }
            },
            function () {
                if (cached) displayCached(cached);
                else getWeather(SEOUL_LAT, SEOUL_LON);
            }
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startWeatherWidget);
    } else {
        startWeatherWidget();
    }
}());
