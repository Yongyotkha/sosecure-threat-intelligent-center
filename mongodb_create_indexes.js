/**
 * MongoDB Index Creation Script for fx_otx_events_indicator_ref
 * 
 * วิธีการรัน:
 * 1. เปิด MongoDB Compass หรือ MongoDB Shell
 * 2. เลือก database: sosecure_threatintelligent
 * 3. รันคำสั่งด้านล่างทีละคำสั่ง
 * 4. หลังจากสร้าง index เสร็จแล้ว ให้ uncomment บรรทัด hint ในไฟล์ IndicatorsController.php
 */

// ========================================
// 1. ตรวจสอบ indexes ที่มีอยู่แล้ว
// ========================================
db.fx_otx_events_indicator_ref.getIndexes();


// ========================================
// 2. สร้าง Index สำหรับ pulse_id (สำคัญที่สุด!)
// ========================================
db.fx_otx_events_indicator_ref.createIndex(
  { "pulse_id": 1 }, 
  { 
    name: "idx_pulse_id",
    background: true  // สร้างแบบ background เพื่อไม่ block database
  }
);


// ========================================
// 3. สร้าง Compound Index สำหรับ pulse_id + updated_at
//    (ใช้สำหรับ query + sort ที่เร็วขึ้น)
// ========================================
db.fx_otx_events_indicator_ref.createIndex(
  { "pulse_id": 1, "updated_at": -1 }, 
  { 
    name: "idx_pulse_id_updated_at",
    background: true
  }
);


// ========================================
// 4. ตรวจสอบว่า indexes ถูกสร้างแล้ว
// ========================================
db.fx_otx_events_indicator_ref.getIndexes();


// ========================================
// 5. ทดสอบ Query Performance
// ========================================
// แทนที่ YOUR_PULSE_ID ด้วย pulse_id จริง เช่น "692cda83d0fdb66f5471190d"
db.fx_otx_events_indicator_ref.find({ "pulse_id": "YOUR_PULSE_ID" })
  .sort({ "updated_at": -1 })
  .limit(25)
  .explain("executionStats");

// ผลลัพธ์ที่ดี:
// - "executionStages.stage" ควรเป็น "IXSCAN" (ใช้ index)
// - "executionStats.totalDocsExamined" ควรใกล้เคียงกับ "executionStats.nReturned"


// ========================================
// 6. (Optional) ลบ index เก่าที่ไม่ใช้แล้ว
// ========================================
// ตรวจสอบก่อนว่ามี index ไหนที่ไม่ได้ใช้
// db.fx_otx_events_indicator_ref.dropIndex("ชื่อ_index_ที่ต้องการลบ");


// ========================================
// 7. หลังจากสร้าง index เสร็จแล้ว
// ========================================
// ไปที่ไฟล์: IndicatorsController.php
// บรรทัด ~1671
// Uncomment บรรทัดนี้:
// 'hint' => ['pulse_id' => 1],
