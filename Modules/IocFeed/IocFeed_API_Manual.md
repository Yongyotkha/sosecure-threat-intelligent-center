# คู่มือการใช้งานระบบ IoC Feed Module API (ฉบับสมบูรณ์)

คู่มือฉบับนี้อธิบายการทำงาน วิธีการเรียกใช้งาน API เงื่อนไข พารามิเตอร์ และตัวอย่างทั้งหมดสำหรับจัดการข้อมูล Indicator of Compromise (IoC) และระบบ Whitelist ซึ่งถูกออกแบบให้รองรับข้อมูลปริมาณมาก (Bulk/Batch Processing) พร้อมกลไกการ Rebuild CSV อัตโนมัติ

> **Base URL:** `/api/v1/ioc-feed`
> **Security:** ทุก Request ต้องส่ง API Token ที่ถูกต้อง (ตรวจสอบสิทธิ์ผ่าน Middleware `api.token`)

---

## 📌 สารบัญ
1. [การส่งออกข้อมูล (Export API)](#1-การส่งออกข้อมูล-export-api)
2. [การจัดการข้อมูล IoC (IoC Management)](#2-การจัดการข้อมูล-ioc-ioc-management)
3. [การจัดการระบบ Whitelist (Whitelist Management)](#3-การจัดการระบบ-whitelist-whitelist-management)
4. [ระบบบันทึกประวัติการทำงาน (Audit & Transaction Log)](#4-ระบบบันทึกประวัติการทำงาน-audit--transaction-log)

---

## 1. การส่งออกข้อมูล (Export API)

### 1.1 `GET /api/v1/ioc-feed/{category}.csv`
ใช้สำหรับดึงข้อมูล IoC ออกมาในรูปแบบไฟล์ข้อความ (CSV format) 

**Category (หมวดหมู่ที่รองรับ)**: `ip_address`, `domain`, `hashfile`, `all`

#### 📥 พารามิเตอร์ (Query String Parameters)
| พารามิเตอร์ | บังคับ | ประเภท | คำอธิบาย |
|:---|:---:|:---:|:---|
| `token` | **ใช่** | String | API Token สำหรับการยืนยันตัวตน (ถ้าใช้ Auth Header อาจละเว้นได้ตามการตั้งค่า) |
| `timeframe` | ไม่ | String | กรองย้อนหลัง (`6h`, `1d`, `1w`, `1m`) |
| `is_public` | ไม่ | Str/Int| กรองข้อมูล Public/Private (`1`, `0`, `all`) |
| `with_whitelist`| ไม่ | Int | ส่ง `1` เพื่อดึงข้อมูลที่ติด Whitelist ออกมาด้วย (Default: ตัดทิ้ง) |

*(💡 Optimization: หากไม่ระบุ timeframe, is_public หรือ with_whitelist ระบบจะอ่านข้อมูลจาก Static CSV ทันทีเพื่อความรวดเร็ว)*

---

## 2. การจัดการข้อมูล IoC (IoC Management)

### 2.1 เพิ่มข้อมูล IoC ใหม่ (`POST /api/v1/ioc-feed/ioc`)
เพิ่มข้อมูลใหม่ หากมี Indicator ซ้ำในระบบ จะทำการอัปเดตข้อมูลเดิม (Upsert)

**พารามิเตอร์ (Body):**
- `indicator` (String) **บังคับ**
- `type` (String) **บังคับ** (`ip_address`, `domain`, `hashfile`)
- `score` (Int) บังคับถ้าไม่มี severity
- `severity` (String) บังคับถ้าไม่มี score
- `category` (String) ไม่บังคับ
- `ioc_timestamp` (String) ไม่บังคับ (เช่น `25/12/2023 14:30`)

### 2.2 แก้ไขข้อมูล IoC (`PUT /api/v1/ioc-feed/ioc/{id}`)
อัปเดตฟิลด์บางส่วน (ส่งเฉพาะค่าที่จะแก้) เช่น `score`, `category`, `severity`, `status`
- `{id}`: อ้างอิงด้วย MongoDB ObjectId

### 2.3 ลบข้อมูล IoC (`DELETE /api/v1/ioc-feed/ioc/{id}`)
ลบข้อมูลออกจากฐานข้อมูลถาวร
- `{id}`: อ้างอิงด้วย MongoDB ObjectId

---

## 3. การจัดการระบบ Whitelist (Whitelist Management)

ระบบ Whitelist ถูกจัดการอย่างมีประสิทธิภาพผ่าน `IocWhitelistController` ซึ่งเมื่อมีการเพิ่มหรือลดข้อมูล ระบบจะไป **Flag/Unflag ข้อมูลในคลัง IoC ให้ทันที (Real-time Filtering)** และสั่ง Rebuild ไฟล์ CSV (Background) โดยอัตโนมัติ

### 3.1 ค้นหารายการ Whitelist (`GET /api/v1/ioc-feed/whitelist`)
รองรับการแบ่งหน้า (Pagination) และการค้นหา
- **พารามิเตอร์:** `type`, `indicator` (รองรับ Regex), `limit` (ค่าเริ่มต้น 50), `page`

### 3.2 เพิ่มข้อมูล Whitelist (`POST /api/v1/ioc-feed/whitelist`)
รองรับทั้งแบบรายการเดียวและแบบหลายรายการ (Batch Array)

**รูปแบบการส่ง (Batch Array):**
```json
{
  "items": [
    {
      "indicator": "8.8.8.8",
      "type": "ip_address",
      "reason": "Google DNS"
    }
  ]
}
```
*(ถ้าเพิ่มสำเร็จ ข้อมูลในคลังหลักจะถูก Flag `is_whitelisted = true` ทันที)*

### 3.3 อัปโหลดไฟล์ Whitelist (`POST /api/v1/ioc-feed/whitelist/upload`)
อัปโหลดรายชื่อจากไฟล์ CSV หรือ TXT (ใช้ Multipart/form-data)
- `file`: ไฟล์ที่ต้องการอัปโหลด (คอลัมน์แรกต้องเป็น Indicator)
- `type`: ประเภท (เช่น `ip_address`)
- `reason`: เหตุผล (ไม่บังคับ)

### 3.4 ลบข้อมูล Whitelist ทีละหลายรายการ (`DELETE /api/v1/ioc-feed/whitelist`)
ลบข้อมูล (ปลดแบน) แบบเป็นกลุ่ม ข้อมูลในคลังจะถูก Unflag คืนสถานะกลับมาทันที

**รูปแบบการส่ง:**
```json
{
  "items": [
    {
      "indicator": "8.8.8.8",
      "type": "ip_address"
    }
  ]
}
```

### 3.5 ลบข้อมูล Whitelist ทีละรายการ (`DELETE /api/v1/ioc-feed/whitelist/{id}`)
- `{id}`: สามารถระบุเป็น **MongoDB ObjectId** หรือระบุเป็น **ค่า Indicator ตรงๆ** ก็ได้ (เช่น `/whitelist/8.8.8.8`)

---

## 4. ระบบบันทึกประวัติการทำงาน (Audit & Transaction Log)

- **Audit Log (`fx_audit_logs`)**: บันทึกการเพิ่ม ลบ แก้ไข ข้อมูล IoC ทั่วไป
- **Transaction Log (`fx_ioc_feed_transactions`)**: เก็บประวัติการเปลี่ยนแปลงสถานะ (Flag/Unflag) จากการทำ Whitelist อย่างละเอียด (ระบุ `ACTION=REMOVE` หรือ `ACTION=UN-WHITELIST`) พร้อมเหตุผล (`reason`) เพื่อความโปร่งใสในการตรวจสอบย้อนหลัง
