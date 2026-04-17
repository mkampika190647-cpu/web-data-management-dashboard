<?php
session_start();
$BASE = "/groupweb_group1"; // ⚙️ ปรับ path โปรเจกต์ให้ถูก
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Apartment Dashboard</title>

  <!-- ✅ CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

  <style>
  body, html {
    margin:0; padding:0;
    font-family:'Roboto',sans-serif;
    background:#f5f7fa;
  }
  .navbar {
    position: fixed;
    top:0; left:0; right:0;
    background:#00796b;
    padding:15px 30px;
    display:flex;
    justify-content:center;
    gap:20px;
    z-index:1000;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
  }
  .navbar a {
    color:white;
    text-decoration:none;
    font-weight:600;
    font-size:18px;
    transition: all 0.3s;
  }
  .navbar a:hover, .navbar a.active {
    color:#b2dfdb;
    transform:scale(1.1);
  }
  .container { margin-top:110px; margin-bottom:60px; }

  .card {
    border-radius:15px;
    border:none;
    box-shadow:0 6px 20px rgba(0,0,0,0.1);
  }
  .card-header {
    background:#00796b;
    color:white;
    font-weight:600;
  }
  .stat-card .num {
    font-size:1.9rem;
    font-weight:700;
    color:#00796b;
  }
  .spinner {
    min-height:140px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .table thead {
    background:#00796b;
    color:white;
  }
  </style>

  <script>const BASE = <?= json_encode($BASE) ?>;</script>
</head>
<body>

<!-- ✅ Navbar -->
<div class="navbar">
  <a href="<?= htmlspecialchars($BASE) ?>/index.php"><i class="fas fa-home"></i> หน้าแรก</a>
  <a href="<?= htmlspecialchars($BASE) ?>/php_file/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
  <a href="<?= htmlspecialchars($BASE) ?>/room.php"><i class="fas fa-building"></i> Room</a>
  <a href="<?= htmlspecialchars($BASE) ?>/customer.php"><i class="fas fa-user"></i> Customer</a>
  <a href="<?= htmlspecialchars($BASE) ?>/bill.php"><i class="fas fa-file-invoice-dollar"></i> Bill</a>
  <a href="<?= htmlspecialchars($BASE) ?>/rate.php"><i class="fas fa-money-bill-wave"></i> Rate</a>
</div>

<!-- ✅ เนื้อหา -->
<div class="container pb-5">

  <!-- [1] ตัวกรองห้อง -->
  <div class="row g-3 mb-3">
    <div class="col-md-6 col-lg-4">
      <label class="form-label mb-1 fw-bold text-secondary">กรองตามห้องพัก</label>
      <select id="filterRoom" class="form-select">
        <option value="">— ทุกห้อง —</option>
      </select>
    </div>
  </div>

  <!-- [2] KPI -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stat-card"><div class="card-body text-center">
        <div class="text-muted">จำนวนห้องทั้งหมด</div>
        <div id="kpiRooms" class="num">—</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card"><div class="card-body text-center">
        <div class="text-muted">จำนวนผู้เช่า</div>
        <div id="kpiCustomers" class="num">—</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card"><div class="card-body text-center">
        <div class="text-muted">ยอดบิลรวม (บาท)</div>
        <div id="kpiTotal" class="num">—</div>
      </div></div>
    </div>
  </div>

  <!-- [3] กราฟ -->
  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>ยอดบิลต่อห้อง</span>
          <button id="btnClearFilter" class="btn btn-sm btn-light d-none">ล้างตัวกรอง</button>
        </div>
        <div class="card-body">
          <div id="wrapBills" class="spinner"><div class="spinner-border text-success" role="status"></div></div>
          <canvas id="chartBills" class="d-none"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header">สัดส่วนสถานะห้อง</div>
        <div class="card-body">
          <div id="wrapStatus" class="spinner"><div class="spinner-border text-success" role="status"></div></div>
          <canvas id="chartStatus" class="d-none"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- [4] ตารางผู้เช่า -->
  <div class="card shadow-sm">
    <div class="card-header">ข้อมูลผู้เช่า</div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblCustomers" class="table table-striped table-bordered w-100">
          <thead>
            <tr>
              <th>รหัสลูกค้า</th>
              <th>ห้อง</th>
              <th>ยอดบิลล่าสุด (บาท)</th>
            </tr>
          </thead>
          <tbody><tr><td colspan="3" class="text-center">กำลังโหลด...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ✅ JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
(() => {
  const qs = s => document.querySelector(s);
  let selectedRoom = '';
  let chartBills = null, chartStatus = null;

  async function fillRoomDropdown() {
    try {
      const r = await fetch(`${BASE}/php_file/get_rooms.php`);
      const arr = await r.json();
      const sel = qs('#filterRoom');
      arr.forEach(room => sel.add(new Option('ห้อง ' + room, room)));
    } catch (e) { console.error('load room error', e); }
  }

  async function loadKPIs(room='') {
    try {
      const url = new URL(`${BASE}/php_file/get_metrics.php`, location.origin);
      if (room) url.searchParams.set('room', room);
      const res = await fetch(url);
      const m = await res.json();
      qs('#kpiRooms').textContent = m.rooms ?? 0;
      qs('#kpiCustomers').textContent = m.customers ?? 0;
      qs('#kpiTotal').textContent = (m.total ?? 0).toLocaleString();
    } catch (e) { console.error('KPI load error', e); }
  }

  async function loadBillsChart(room='') {
  try {
    const url = new URL(`${BASE}/php_file/get_bills_table.php`, location.origin);
    if (room) url.searchParams.set('room', room);
    const r = await fetch(url);
    const rows = await r.json();
    if (!rows.length) {
      qs('#wrapBills').innerHTML = 'ไม่มีข้อมูลบิล';
      return;
    }
    
    qs('#wrapBills').classList.add('d-none');
    const ctx = qs('#chartBills');
    ctx.classList.remove('d-none');
    
    const labels = rows.map(r => 'ห้อง ' + r.Room_Number); // ตั้งชื่อห้อง
    const data = rows.map(r => r.Total_Bill ?? 0); // ยอดบิลรวม

    if (chartBills) chartBills.destroy(); // ทำลายกราฟเดิมก่อนหากมี
    chartBills = new Chart(ctx, {
      type: 'line', // เปลี่ยนประเภทเป็น 'line'
      data: {
        labels,
        datasets: [{
          label: 'ยอดรวม (บาท)', 
          data,
          fill: false,  // ทำให้กราฟไม่เต็มสี
          borderColor: '#00796b', // สีเส้นกราฟ
          tension: 0.4,  // ความโค้งของเส้น
          pointRadius: 5, // ขนาดของจุดบนเส้น
          pointBackgroundColor: '#26a69a', // สีของจุด
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false, // ซ่อน legend
          },
        },
        scales: {
          y: {
            beginAtZero: true, // เริ่มจาก 0 ที่แกน Y
            ticks: {
              callback: function(value) {
                return value.toLocaleString(); // แสดงยอดบิลในรูปแบบตัวเลขที่มีคอมม่า
              }
            }
          },
          x: {
            title: {
              display: true,
              text: 'ห้อง' // ตั้งชื่อแกน X
            }
          }
        }
      }
    });
  } catch (e) {
    console.error('bills chart error', e);
  }
}


  async function loadStatusChart() {
    try {
      const r = await fetch(`${BASE}/php_file/get_room_status_dist.php`);
      const rows = await r.json();
      if (!rows.length) { qs('#wrapStatus').innerHTML = 'ไม่มีข้อมูลสถานะ'; return; }
      qs('#wrapStatus').classList.add('d-none');
      const ctx = qs('#chartStatus');
      ctx.classList.remove('d-none');
      const labels = rows.map(x=>x.status);
      const data = rows.map(x=>x.cnt);
      if (chartStatus) chartStatus.destroy();
      chartStatus = new Chart(ctx, {
        type:'doughnut',
        data:{labels,datasets:[{data,backgroundColor:['#00796b','#26a69a','#b2dfdb']}]},
        options:{plugins:{legend:{position:'bottom'}}}
      });
    } catch (e) { console.error('status chart error', e); }
  }

  async function loadCustomersTable(room='') {
    try {
      const url = new URL(`${BASE}/php_file/get_customers_table.php`, location.origin);
      if (room) url.searchParams.set('room', room);
      const r = await fetch(url);
      const rows = await r.json();
      const data = rows.map(r=>({CustomerID:r.CustomerID,Room_Number:r.Room_Number,Total_Bill:r.Total_Bill}));
      const jq = window.jQuery;
      jq(function(){
        if (jq.fn.dataTable.isDataTable('#tblCustomers')) {
          jq('#tblCustomers').DataTable().clear().rows.add(data).draw();
        } else {
          jq('#tblCustomers').DataTable({
            data,
            columns:[{data:'CustomerID'},{data:'Room_Number'},{data:'Total_Bill'}],
            pageLength:10
          });
        }
      });
    } catch (e) { console.error('table error', e); }
  }

  async function applyFilter() {
    await loadKPIs(selectedRoom);
    await loadBillsChart(selectedRoom);
    await loadStatusChart();
    await loadCustomersTable(selectedRoom);
  }

  qs('#filterRoom').addEventListener('change', e => {
    selectedRoom = e.target.value;
    applyFilter();
  });

  fillRoomDropdown();
  applyFilter();
})();
</script>
</body>
</html>
