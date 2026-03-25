# Service Receive API Documentation

## Overview

API สำหรับรับข้อมูล Threat Intelligence Events และ Indicators จากแหล่งข้อมูลภายนอก

---

## Authentication

ใช้ **Bearer Token** ใน Header:

```
Authorization: Bearer YOUR_SERVICE_RECEIVE_API_TOKEN
```

### วิธีสร้าง Token:
1. ไปที่ **System Settings**
2. หา section **"Service Receive API Token"**
3. กดปุ่ม **Generate**
4. Copy token ไปใช้งาน

---

## Endpoint

```
POST /api/v1/service/events
```

### Headers

| Header | Value |
|--------|-------|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |
| `Authorization` | `Bearer YOUR_TOKEN` |

---

## Request Body

### Event Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ❌ | Event ID (auto-generate ถ้าไม่ส่ง) |
| `info` | string | ✅ | ชื่อ/หัวข้อ Event |
| `date` | string | ❌ | วันที่สร้าง (YYYY-MM-DD) |
| `modified` | string | ❌ | วันที่แก้ไข (YYYY-MM-DD HH:mm:ss) |
| `timestamp` | int | ❌ | Unix timestamp |
| `published` | bool | ❌ | เผยแพร่หรือยัง (default: true) |
| `threat_level_id` | int | ❌ | ระดับภัยคุกคาม (1-4) |
| `orgc_id` | string | ❌ | ชื่อองค์กรผู้สร้าง |
| `Tag` | array | ❌ | Tags ของ Event |

### Attribute Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ❌ | Attribute ID |
| `type` | string | ✅ | ประเภท (ip-dst, domain, md5, etc.) |
| `value` | string | ✅ | ค่า IOC |
| `category` | string | ❌ | หมวดหมู่ (default: Network activity) |
| `comment` | string | ❌ | หมายเหตุ |
| `score` | int | ❌ | คะแนนความเสี่ยง (0-100) |
| `severity` | string | ❌ | ระดับ (low/medium/high/critical) |
| `confidence` | int | ❌ | ความมั่นใจ (0-100) |
| `to_ids` | bool | ❌ | ใช้ใน IDS (default: true) |
| `Tag` | array | ❌ | Tags ของ Attribute |

---

## Examples

### Basic Example

```json
{
  "Event": {
    "info": "Malware Campaign Alert",
    "modified": "2026-01-21 17:00:00",
    "orgc_id": "SECURITY_TEAM",
    "Attribute": [
      {
        "type": "ip-dst",
        "value": "192.168.1.100"
      }
    ]
  }
}
```

### Full Example

```json
{
  "Event": {
    "id": "apt-2026-001",
    "info": "APT Campaign Targeting Financial Sector",
    "date": "2026-01-21",
    "modified": "2026-01-21 17:35:00",
    "published": true,
    "threat_level_id": 2,
    "orgc_id": "THREAT_INTEL_TEAM",
    "Tag": [
      {"name": "tlp:amber"},
      {"name": "apt"},
      {"name": "banking"}
    ],
    "Attribute": [
      {
        "id": "ioc-001",
        "type": "ip-dst",
        "value": "185.220.101.55",
        "category": "Network activity",
        "comment": "C2 Server",
        "score": 95,
        "severity": "critical",
        "Tag": [{"name": "c2"}]
      },
      {
        "id": "ioc-002",
        "type": "domain",
        "value": "malware.xyz",
        "category": "Network activity",
        "score": 80,
        "severity": "high"
      }
    ]
  }
}
```

### Dry Run (Preview)

เพิ่ม `"dry_run": true` เพื่อดู preview ก่อน save:

```json
{
  "dry_run": true,
  "Event": {
    "info": "Test Event",
    ...
  }
}
```

---

## Response

### Success (201)

```json
{
  "success": true,
  "message": "Event received successfully",
  "data": {
    "pulse_id": "svc.apt-2026-001",
    "event_name": "APT Campaign Targeting Financial Sector",
    "indicators_count": 2
  }
}
```

### Dry Run Success (200)

```json
{
  "success": true,
  "dry_run": true,
  "message": "Dry run - data preview (not saved)",
  "data": {
    "fx_otx_events": { ... },
    "fx_otx_indicator_detail": [ ... ],
    "fx_otx_events_indicator_ref": [ ... ]
  }
}
```

### Error (401)

```json
{
  "error": "Unauthorized",
  "message": "Invalid API token or wrong token type"
}
```

### Error (422)

```json
{
  "error": "Validation failed",
  "message": "Event info/name is required"
}
```

---

## Attribute Types

| Type | Description |
|------|-------------|
| `ip-dst` | IP ปลายทาง |
| `ip-src` | IP ต้นทาง |
| `domain` | โดเมน |
| `hostname` | ชื่อ host |
| `url` | URL |
| `md5` | MD5 hash |
| `sha1` | SHA1 hash |
| `sha256` | SHA256 hash |
| `email-src` | อีเมลผู้ส่ง |
| `email-dst` | อีเมลผู้รับ |
| `filename` | ชื่อไฟล์ |
| `mutex` | Mutex name |
| `regkey` | Registry key |

---

## Notes

- ข้อมูลจะถูก save ลง MongoDB ใน collections:
  - `fx_otx_events` - Event data
  - `fx_otx_indicator_detail` - Indicator details  
  - `fx_otx_events_indicator_ref` - Event-Indicator references
- `source` จะถูกตั้งเป็น `"service_api"` อัตโนมัติ
- `pulse_id` จะมี prefix `"svc."` ตามด้วย Event ID ที่ส่งมา
