# MISP Tag Sync - คู่มือการใช้งาน

## 📋 ภาพรวม

ระบบซิงค์ tags จาก MongoDB temp collections ไปยัง MISP โดยอัตโนมัติ

## 🎯 ฟีเจอร์

- ✅ **Auto Skip**: ข้ามถ้าไม่มี tags (ไม่อัพเดท MISP)
- ✅ **Scheduler**: รันทุกวันอัตโนมัติ
- ✅ **Backlog Processing**: ประมวลผลข้อมูลย้อนหลัง
- ✅ **Performance**: เร็วขึ้น 10-15 เท่า (tag cache, batch insert)
- ✅ **Safe**: Transaction, duplicate prevention

## 🚀 การใช้งาน

### 1. Scheduler (รันทุกวัน)

**ตั้งค่าแล้ว:** รันทุกวันเวลา 04:00 น.

```bash
# เช็คว่า scheduler ทำงานหรือไม่
php artisan schedule:list

# ทดสอบรัน scheduler
php artisan schedule:run
```

**ตั้ง Windows Task Scheduler:**
```
Program: php
Arguments: d:\projects\threat-intelligent\artisan schedule:run
Schedule: ทุกนาที
```

**หรือใช้ Cron (Linux):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

### 2. ประมวลผลย้อนหลัง (Backlog)

**สำหรับข้อมูลเก่า 600,000 records:**

```bash
# วิธีที่ 1: ใช้ Script (แนะนำ)
.\sync_backlog.ps1

# วิธีที่ 2: รันเอง
php artisan misp:tags:sync-insight --window=2880 --max-total=10000
```

**Script จะ:**
- รันทีละ 10,000 records
- แสดง progress
- วนไปเรื่อยๆ จนครบ
- หยุดอัตโนมัติเมื่อเสร็จ

---

### 3. รันแบบ Manual

```bash
# ทดสอบ (ไม่บันทึก)
php artisan misp:tags:sync-insight --dry-run --max-total=10

# รันจริง
php artisan misp:tags:sync-insight --max-total=1000

# รันเฉพาะ event
php artisan misp:tags:sync-insight --only=event --max-total=100

# รันเฉพาะ indicator
php artisan misp:tags:sync-insight --only=indicator --max-total=100

# ย้อนหลัง 7 วัน
php artisan misp:tags:sync-insight --window=168 --max-total=1000
```

---

## 📊 ติดตามความคืบหน้า

### MongoDB

```javascript
// ดูว่าทำไปกี่ record
db.fx_events_temp.countDocuments({status: 'done'})
db.fx_indicators_temp.countDocuments({status: 'done'})

// ดูว่าเหลืออีกกี่ record
db.fx_events_temp.countDocuments({
    $or: [{status: {$ne: 'done'}}, {status: {$exists: false}}]
})

// คำนวณ %
var done = db.fx_indicators_temp.countDocuments({status: 'done'})
var total = db.fx_indicators_temp.countDocuments({})
print("Progress: " + (done/total*100).toFixed(2) + "%")
```

### Log

```bash
# ดู log real-time
Get-Content storage\logs\laravel-2025-12-08.log -Tail 50 -Wait

# หา error
Select-String -Path storage\logs\laravel-2025-12-08.log -Pattern "ERROR"
```

---

## ⚙️ Options

| Option | Default | คำอธิบาย |
|--------|---------|----------|
| `--window` | 6 | ย้อนหลังกี่ชั่วโมง |
| `--max-total` | 50000 | จำกัดจำนวนรวม |
| `--batch` | 1000 | จำนวน records ต่อรอบ |
| `--batch-size` | 500 | MongoDB cursor batch size |
| `--only` | - | `event` หรือ `indicator` |
| `--dry-run` | false | ทดสอบไม่บันทึก |
| `--ignore-checkpoint` | false | ไม่ใช้ checkpoint |

---

## 🔧 Troubleshooting

### ปัญหา: ไม่มีข้อมูล (total=0)

**สาเหตุ:** ข้อมูลเก่ากว่า window

**แก้ไข:**
```bash
# เพิ่ม window
php artisan misp:tags:sync-insight --window=2880 --max-total=10
```

### ปัญหา: ช้า

**แก้ไข:**
```bash
# เพิ่ม batch size
php artisan misp:tags:sync-insight --batch=2000 --batch-size=1000
```

### ปัญหา: Memory

**แก้ไข:**
- ลด `--max-total`
- ลด `--batch`
- เพิ่ม PHP memory limit

---

## 📝 หมายเหตุ

1. **ไม่มี tags = ข้าม**: ถ้า record ไม่มี tags จะ mark เป็น 'done' โดยไม่อัพเดท MISP
2. **Duplicate safe**: ไม่มี tag ซ้ำใน event/indicator
3. **Resume ได้**: ใช้ checkpoint สามารถหยุดแล้วรันต่อได้
4. **Transaction**: ปลอดภัย ถ้า error ข้อมูลไม่เสีย

---

## 🎊 สรุป

- **Daily Sync**: รันอัตโนมัติทุกวัน 04:00
- **Backlog**: ใช้ `sync_backlog.ps1` ประมวลผลย้อนหลัง
- **Monitor**: ดู log และ MongoDB
- **Performance**: เร็วขึ้น 10-15 เท่า

**ติดปัญหาดู log:** `storage/logs/laravel-YYYY-MM-DD.log`
