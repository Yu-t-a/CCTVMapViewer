<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผนที่ CCTV MAP</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <style>
    body {
        margin: 0;
        padding: 0;
    }

    #map {
        height: 100vh;
        /* Full viewport height */
        width: 100%;
    }

    /* Base marker icon styles */
    .leaflet-marker-icon.transport-marker {
        background-color: #E91E63;
        /* Pink color from the image */
        border-radius: 50%;
        /* Circular shape */
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-weight: bold;
        font-family: Arial, sans-serif;
    }

    /* Size variations */
    .transport-marker-xs {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }

    .transport-marker-sm {
        width: 30px;
        height: 30px;
        font-size: 14px;
    }

    .transport-marker-md {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .transport-marker-lg {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }

    .transport-marker-xl {
        width: 60px;
        height: 60px;
        font-size: 26px;
    }

    /* Status-based colors (optional) */
    .transport-marker.status-online {
        background-color: #4CAF50;
        /* Green for Online */
    }

    .transport-marker.status-offline {
        background-color: #FF0000;
        /* Red for Offline */
    }


    .transport-marker.status-rc {
        background-color: rgb(38, 174, 238);
        /* Red for Offline */
    }
    </style>
</head>

<body>

    <div id="map"></div>

    <script>
    var map = L.map('map').setView([12.9355898753118, 100.88998498907718], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    function getFontSizeBasedOnZoom(zoomLevel) {
        if (zoomLevel <= 10) {
            return '12px';
        } else if (zoomLevel <= 15) {
            return '9px';
        } else {
            return '9px';
        }
    }

    let markers = []; // เก็บรายการ marker ทั้งหมด

    var markerClusterGroup = L.markerClusterGroup({
        // สามารถปรับแต่งได้ เช่น
        maxClusterRadius: 50, // รัศมีในการรวมกลุ่ม (pixel)
        disableClusteringAtZoom: 16, // ระดับ zoom ที่จะไม่รวมกลุ่ม
        spiderfyOnMaxZoom: true, // กระจาย marker เมื่อคลิกที่ cluster
        iconCreateFunction: function(cluster) {
            // กำหนดสีและขนาดของ cluster icon
            var childCount = cluster.getChildCount();
            var c = ' marker-cluster-';
            if (childCount < 10) {
                c += 'small';
            } else if (childCount < 100) {
                c += 'medium';
            } else {
                c += 'large';
            }
            return L.divIcon({
                html: '<div><span>' + childCount + '</span></div>',
                className: 'marker-cluster' + c,
                iconSize: L.point(40, 40)
            });
        }
    }).addTo(map);

    function convertToThaiDate(dateString) {
        if (!dateString) return 'ไม่มีข้อมูล';

        // สร้าง Date object จากสตริง
        const date = new Date(dateString);

        // ตรวจสอบว่าวันที่ถูกต้อง
        if (isNaN(date.getTime())) return 'วันที่ไม่ถูกต้อง';

        // สร้าง Intl.DateTimeFormat สำหรับภาษาไทย
        const thaiDateFormatter = new Intl.DateTimeFormat('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        return thaiDateFormatter.format(date);
    }
    // แก้ไขใน fetch function
    fetch('cctv_data.php')
        .then(response => response.json())
        .then(data => {
            data.forEach((item, index) => {
                var lat = parseFloat(item.Latitude);
                var lon = parseFloat(item.Longitude);

                if (isNaN(lat) || isNaN(lon)) {
                    console.warn(`ข้อมูลพิกัดไม่ถูกต้องสำหรับกล้อง: ${item.camera_name}`);
                    return;
                }

                // กำหนด statusClass ใหม่
                var statusClass = 'status-offline'; // ค่าเริ่มต้น
                if (item.cctv_Online_status === 'Online') {
                    statusClass = 'status-online';
                }

                // เพิ่มเงื่อนไข Project RCS
                if (item.Project === 'RCS') {
                    statusClass = 'status-rc';
                }

                // กำหนดวันที่ล่าสุด
                var latestStatusDate = item.cctv_Online_status === 'Online' ?
                    item.Use_Status_Date :
                    item.Not_Use_date;

                var latestStatus = item.cctv_Online_status === 'Online' ?
                    "ปกติ" :
                    "ไม่ปกติ";

                let markerText = item.NewCode || (index + 1);

                var zoomLevel = map.getZoom();
                var fontSize = getFontSizeBasedOnZoom(zoomLevel);
                var iconSize = getIconSizeBasedOnZoom(zoomLevel);

                var iconDiv = L.divIcon({
                    className: `transport-marker ${statusClass}`,
                    html: `<div style="font-size: ${fontSize};">${markerText}</div>`,
                    iconSize: iconSize,
                    iconAnchor: [iconSize[0] / 2, iconSize[1]]
                });

                var marker = L.marker([lat, lon], {
                    icon: iconDiv,
                    markerText: markerText,
                    statusClass: statusClass
                });
                // `ใช้งานล่าสุด: ${convertToThaiDate(latestStatusDate)}<br>` +
                marker.bindPopup(
                    `<b>กล้อง: ${item.camera_name}</b><br>` +
                    `โครงการ: ${item.Project || 'ไม่ระบุ'}<br>` +
                    `สถานะ: ${latestStatus}<br>`
                );

                markerClusterGroup.addLayer(marker);
                markers.push(marker);
            });
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
        });

    // Event listener เมื่อมีการซูม
    function getIconSizeBasedOnZoom(zoomLevel) {
        // กำหนดขนาด icon ตามระดับ zoom
        if (zoomLevel <= 10) {
            return [30, 30]; // เล็กมาก
        } else if (zoomLevel <= 12) {
            return [30, 30]; // เล็ก
        } else if (zoomLevel <= 14) {
            return [30, 30]; // กลาง
        } else if (zoomLevel <= 16) {
            return [30, 30]; // ใหญ่
        } else {
            return [30, 30]; // ใหญ่มาก
        }
    }

    function getFontSizeBasedOnZoom(zoomLevel) {
        // if (zoomLevel <= 10) {
        //     return '8px';
        // } else if (zoomLevel <= 15) {
        //     return '9px';
        // } else {
        //     return '10px';
        // }

        // กำหนดขนาด icon ตามระดับ zoom
        if (zoomLevel <= 10) {
            return '8px';
        } else if (zoomLevel <= 12) {
            return '8px';
        } else if (zoomLevel <= 14) {
            return '8px';
        } else if (zoomLevel <= 16) {
            return '8px';
        } else {
            return '8px';
        }
    }

    // แก้ไข event listener เมื่อ zoom
    map.on('zoomend', function() {
        var zoomLevel = map.getZoom();
        // console.log('Current Zoom Level:', zoomLevel);
        var fontSize = getFontSizeBasedOnZoom(zoomLevel);
        var iconSize = getIconSizeBasedOnZoom(zoomLevel);

        markers.forEach(marker => {
            var updatedIcon = L.divIcon({
                className: `transport-marker ${marker.options.statusClass}`,
                html: `<div style="font-size: ${fontSize};">${marker.options.markerText}</div>`,
                iconSize: iconSize,
                iconAnchor: [iconSize[0] / 2, iconSize[1]] // ปรับ anchor ตามขนาดใหม่
            });

            marker.setIcon(updatedIcon);
        });
    });

    // // เพิ่ม event listener สำหรับการเลื่อน mouse wheel
    // map.on('wheel', function(e) {
    //     var zoomLevel = map.getZoom();
    //     console.log('Mouse Wheel Zoom Level:', zoomLevel);
    // });

    // ใช้ฟังก์ชันนี้ตอนสร้าง marker ครั้งแรกด้วย
    var zoomLevel = map.getZoom();
    var fontSize = getFontSizeBasedOnZoom(zoomLevel);
    var iconSize = getIconSizeBasedOnZoom(zoomLevel);

    var iconDiv = L.divIcon({
        className: `transport-marker ${statusClass}`,
        html: `<div style="font-size: ${fontSize};">${markerText}</div>`,
        iconSize: iconSize,
        iconAnchor: [iconSize[0] / 2, iconSize[1]]
    });
    </script>



</body>

</html>