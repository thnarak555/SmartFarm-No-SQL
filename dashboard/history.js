const API_BASE_URL = '/api'; 

const sensorHistoryBody = document.getElementById('sensor-history-body');
const deviceHistoryBody = document.getElementById('device-history-body');
const connectionStatus = document.getElementById('connection-status');
const lastUpdateTime = document.getElementById('last-update-time');
let sensorChart = null;

function formatDateTime(datetimeStr) {
    const timeObj = new Date(datetimeStr);
    return timeObj.toLocaleString('th-TH', { 
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
}

function updateStatus(isOnline) {
    if (isOnline) {
        connectionStatus.textContent = 'System Online';
        connectionStatus.className = 'status-indicator online';
    } else {
        connectionStatus.textContent = 'System Offline / Error';
        connectionStatus.className = 'status-indicator offline';
    }
    
    const now = new Date();
    lastUpdateTime.textContent = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

async function fetchSensorHistory() {
    try {
        const response = await fetch(API_BASE_URL);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        
        if (data.success) {
            const sensors = data.data.filter(d => d.device_type === 'SENSOR').map(s => ({
                sensor_name: s.device_name,
                sensor_value: s.status,
                unit: s.device_name === 'Temperature' ? '°C' : (s.device_name === 'Humidity' || s.device_name === 'Soil Moisture' ? '%' : ''),
                datetime: s.created_at
            }));
            renderSensorHistory(sensors);
            renderChart(sensors);
            updateStatus(true);
        }
    } catch (error) {
        console.error('Error fetching sensor history:', error);
        updateStatus(false);
        sensorHistoryBody.innerHTML = '<tr><td colspan="3" class="loading">Failed to load data.</td></tr>';
    }
}

function renderSensorHistory(records) {
    if (!records || records.length === 0) {
        sensorHistoryBody.innerHTML = '<tr><td colspan="3" class="loading">No sensor history found.</td></tr>';
        return;
    }

    let html = '';
    records.forEach(record => {
        html += `
            <tr>
                <td>${formatDateTime(record.datetime)}</td>
                <td class="td-highlight">${record.sensor_name}</td>
                <td>${record.sensor_value} <span style="font-size: 0.8rem; color: var(--text-sec);">${record.unit}</span></td>
            </tr>
        `;
    });
    
    sensorHistoryBody.innerHTML = html;
}

function renderChart(records) {
    if (!records || records.length === 0) return;

    // ข้อมูลจาก API เป็น DESC (ล่าสุดอยู่บนสุด) 
    // สำหรับกราฟ เราต้องกลับด้านให้เป็น ASC (เวลาวิ่งจากซ้ายไปขวา)
    const reversedRecords = [...records].reverse();

    // รวบรวมเวลาทั้งหมดแบบไม่ซ้ำกัน
    const timeSet = new Set();
    reversedRecords.forEach(r => timeSet.add(r.datetime));
    const timeArray = Array.from(timeSet).sort();
    
    // จัดกลุ่มข้อมูลตามชื่อเซ็นเซอร์
    const sensorGroups = {};
    reversedRecords.forEach(r => {
        if (!sensorGroups[r.sensor_name]) {
            sensorGroups[r.sensor_name] = {};
        }
        sensorGroups[r.sensor_name][r.datetime] = r.sensor_value;
    });

    // แปลงเวลาให้เป็น String สั้นๆ สำหรับป้ายกำกับแกน X
    const labels = timeArray.map(t => {
        const d = new Date(t);
        return d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
    });

    // กำหนดสีให้เซ็นเซอร์แต่ละตัว
    const colors = {
        'Temperature': '#f87171', // แดง
        'Humidity': '#3b82f6', // ฟ้า
        'Soil Moisture': '#34d399', // เขียว
        'default': '#a78bfa' // ม่วง (ถ้ามีเซ็นเซอร์อื่น)
    };

    // สร้าง Datasets สำหรับ Chart.js
    const datasets = Object.keys(sensorGroups).map(sensorName => {
        // ดึงค่าตามเวลา ถ้าเวลาไหนไม่มีข้อมูลของเซ็นเซอร์นี้ให้เป็น null
        const data = timeArray.map(t => sensorGroups[sensorName][t] || null); 
        const color = colors[sensorName] || colors['default'];
        
        return {
            label: sensorName,
            data: data,
            borderColor: color,
            backgroundColor: color,
            tension: 0.4, // ทำให้เส้นโค้งมนสมูท
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#111827',
            spanGaps: true // ลากเส้นข้ามค่าที่เป็น null
        };
    });

    const ctx = document.getElementById('sensorChart').getContext('2d');
    
    // ตั้งค่าสีตัวอักษรของ Chart.js ให้เข้ากับ Dark Theme
    Chart.defaults.color = 'rgba(255, 255, 255, 0.6)';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';

    if (sensorChart) {
        // อัปเดตข้อมูลให้กราฟเดิม
        sensorChart.data.labels = labels;
        sensorChart.data.datasets = datasets;
        sensorChart.update();
    } else {
        // สร้างกราฟใหม่
        sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'top',
                        labels: { boxWidth: 12, usePointStyle: true }
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: false }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
}

async function fetchDeviceHistory() {
    try {
        const response = await fetch(API_BASE_URL);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        
        if (data.success) {
            const leds = data.data.filter(d => d.device_type === 'LED').map(l => ({
                device_name: l.device_name,
                status: l.status,
                datetime: l.created_at
            }));
            renderDeviceHistory(leds);
        }
    } catch (error) {
        console.error('Error fetching device history:', error);
        deviceHistoryBody.innerHTML = '<tr><td colspan="3" class="loading">Failed to load data.</td></tr>';
    }
}

function renderDeviceHistory(records) {
    if (!records || records.length === 0) {
        deviceHistoryBody.innerHTML = '<tr><td colspan="3" class="loading">No device history found.</td></tr>';
        return;
    }

    let html = '';
    records.forEach(record => {
        const isON = record.status === 'ON';
        const badgeClass = isON ? 'badge-on' : 'badge-off';
        
        html += `
            <tr>
                <td>${formatDateTime(record.datetime)}</td>
                <td class="td-highlight">${record.device_name}</td>
                <td><span class="badge ${badgeClass}">${record.status}</span></td>
            </tr>
        `;
    });
    
    deviceHistoryBody.innerHTML = html;
}

function init() {
    fetchSensorHistory();
    fetchDeviceHistory();
}

document.addEventListener('DOMContentLoaded', () => {
    init();
    
    // Test Data Generation Logic
    const btnGenerateData = document.getElementById('btn-generate-data');
    if (btnGenerateData) {
        btnGenerateData.addEventListener('click', async () => {
            btnGenerateData.disabled = true;
            btnGenerateData.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            
            const sensors = [
                { name: 'Temperature', unit: '°C', min: 25, max: 35 },
                { name: 'Humidity', unit: '%', min: 40, max: 80 },
                { name: 'Soil Moisture', unit: '%', min: 30, max: 70 }
            ];

            // สร้างข้อมูลย้อนหลัง 20 จุด (สมมติว่าจุดละ 5 นาที)
            const now = new Date();
            
            for (let i = 20; i >= 0; i--) {
                const pastTime = new Date(now.getTime() - (i * 5 * 60000));
                // ชดเชย Timezone (ไทย +7) เพื่อให้ได้เวลา Local แท้จริงสำหรับใส่ลง Database
                const offset = pastTime.getTimezoneOffset() * 60000;
                const localTime = new Date(pastTime.getTime() - offset);
                const mysqlTime = localTime.toISOString().slice(0, 19).replace('T', ' ');

                for (const s of sensors) {
                    // สุ่มค่า
                    const val = (Math.random() * (s.max - s.min) + s.min).toFixed(1);
                    
                    try {
                        await fetch(API_BASE_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                sensor_name: s.name,
                                sensor_value: val,
                                unit: s.unit,
                                datetime: mysqlTime
                            })
                        });
                    } catch (e) {
                        console.error('Failed to post test data', e);
                    }
                }
            }
            
            // Re-fetch to update table and chart
            fetchSensorHistory();
            
            btnGenerateData.innerHTML = '<i class="fa-solid fa-check"></i> Done!';
            setTimeout(() => {
                btnGenerateData.innerHTML = '<i class="fa-solid fa-flask"></i> Generate Test Data';
                btnGenerateData.disabled = false;
            }, 2000);
        });
    }
});
