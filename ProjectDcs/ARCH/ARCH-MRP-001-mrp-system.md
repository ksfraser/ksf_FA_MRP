# ARCH-MRP-001 - MRP System Architecture

**Business Requirement**: BR-MRP-001
**Module**: ksf_FA_MRP
**Status**: Proposed
**PHP Version**: 7.3+

---

## 1. System Context

### 1.1 MRP Data Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          MRP SYSTEM CONTEXT                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌──────────────┐    ┌──────────────────────────────────────────────┐  │
│  │   DEMANDS    │    │              MRP ENGINE                       │  │
│  ├──────────────┤    │  ┌────────────────────────────────────────┐  │  │
│  │ Sales Orders │───▶│  │ 1. Demand Aggregation                  │  │  │
│  │ Forecasts    │    │  │ 2. BOM Explosion                       │  │  │
│  │ Safety Stock │    │  │ 3. Net Requirements Calc               │  │  │
│  │ Work Orders  │    │  │ 4. Lot Sizing                           │  │  │
│  └──────────────┘    │  │ 5. Planned Order Generation            │  │  │
│                      │  │ 6. Action Message Generation           │  │  │
│                      │  └────────────────────────────────────────┘  │  │
│                      └──────────────────┬───────────────────────────┘  │
│                                         │                                │
│                                         ▼                                │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                      MRP OUTPUTS                                 │   │
│  ├─────────────────┬──────────────────┬──────────────────────────────┤   │
│  │ Planned Orders  │ Action Messages  │ Material Requirements       │   │
│  │ (Purchasing)    │ (Reschedule/     │ (Shortage Analysis)          │   │
│  │                 │  Cancel)         │                              │   │
│  └─────────────────┴──────────────────┴──────────────────────────────┘   │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 1.2 MRP Time Bucket Logic

| Time Bucket | Monday | Tuesday | Wednesday | Thursday | Friday |
|-------------|--------|---------|-----------|----------|--------|
| Week 1      | X      | X       | X         | X        | X      |
| Week 2      | X      | X       | X         | X        | X      |
| ...         |        |         |           |          |        |

MRP explodes demands week-by-week, calculating net requirements per time bucket.

---

## 2. MRP Engine Component

### 2.1 MRP Engine Class

```php
<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\MRP;

class MRPEngine
{
    const VERSION = '1.0.0';

    private $db;
    private $planningHorizon;
    private $timeBucket;
    private $lotSizingMethod;

    public function __construct(DbConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function setPlanningHorizon(int $days): void
    {
        $this->planningHorizon = $days;
    }

    public function setTimeBucket(string $bucket): void
    {
        $this->timeBucket = $bucket;
    }

    public function setLotSizingMethod(string $method): void
    {
        $this->lotSizingMethod = $method;
    }

    public function runMRP(): array
    {
        $this->clearPlannedOrders();
        $demands = $this->aggregateDemands();
        $exploded = $this->explodeBOM($demands);
        $netReqs = $this->calculateNetRequirements($exploded);
        $plannedOrders = $this->generatePlannedOrders($netReqs);
        $this->savePlannedOrders($plannedOrders);
        return $this->generateActionMessages($plannedOrders);
    }

    private function clearPlannedOrders(): void
    {
        $this->db->executeUpdate("DELETE FROM 0_mrp_planned_orders");
        $this->db->executeUpdate("DELETE FROM 0_mrp_action_messages");
    }
}
```

### 2.2 MRP Parameters Table

**Table**: `0_mrp_parameters`

| Column | Type | Description |
|--------|------|-------------|
| parameter_id | VARCHAR(50) | Primary key |
| parameter_value | TEXT | Parameter value |
| description | VARCHAR(255) | Description |
| updated_at | TIMESTAMP | Last update |

**Default Parameters**:

| Parameter | Default | Description |
|-----------|---------|-------------|
| planning_horizon_days | 30 | Days ahead to plan |
| time_bucket | WEEK | Time bucket (DAY/WEEK) |
| lot_sizing_method | LFL | Default lot sizing |
| safety_stock_method | NONE | Safety stock calculation |
| phantom_dissolve | 1 | Dissolve phantom BOMs |
| low_level_code | 1 | Low-level coding enabled |

---

## 3. Database Schema

### 3.1 0_mrp_parameters

```sql
CREATE TABLE IF NOT EXISTS `0_mrp_parameters` (
    `parameter_id` VARCHAR(50) NOT NULL,
    `parameter_value` TEXT,
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.2 0_mrp_demands

```sql
CREATE TABLE IF NOT EXISTS `0_mrp_demands` (
    `demand_id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_code` VARCHAR(20) NOT NULL,
    `demand_type` VARCHAR(20) NOT NULL COMMENT 'SO=Sales Order, WO=Work Order, FC=Forecast',
    `source_id` VARCHAR(20) DEFAULT NULL COMMENT 'Sales order or work order reference',
    `quantity` DECIMAL(15,3) NOT NULL,
    `due_date` DATE NOT NULL,
    `priority` INT(11) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`demand_id`),
    KEY `idx_item_code` (`item_code`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_demand_type` (`demand_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.3 0_mrp_planned_orders

```sql
CREATE TABLE IF NOT EXISTS `0_mrp_planned_orders` (
    `order_id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_code` VARCHAR(20) NOT NULL,
    `order_type` VARCHAR(10) NOT NULL COMMENT 'PO=Purchase, WO=Work Order',
    `quantity` DECIMAL(15,3) NOT NULL,
    `due_date` DATE NOT NULL,
    `start_date` DATE NOT NULL,
    `source_demand_id` INT(11) DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT 'PLANNED',
    `firm` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`order_id`),
    KEY `idx_item_code` (`item_code`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.4 0_mrp_action_messages

```sql
CREATE TABLE IF NOT EXISTS `0_mrp_action_messages` (
    `message_id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_code` VARCHAR(20) NOT NULL,
    `action_type` VARCHAR(20) NOT NULL COMMENT 'ORDER, RESCHEDULE, CANCEL',
    `old_date` DATE DEFAULT NULL,
    `new_date` DATE DEFAULT NULL,
    `old_quantity` DECIMAL(15,3) DEFAULT NULL,
    `new_quantity` DECIMAL(15,3) DEFAULT NULL,
    `priority` INT(11) DEFAULT 1,
    `acknowledged` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`),
    KEY `idx_item_code` (`item_code`),
    KEY `idx_action_type` (`action_type`),
    KEY `idx_acknowledged` (`acknowledged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. Integration with FA Modules

### 4.1 FA BOM Integration

MRP reads Bill of Materials from `0_bom`:

```php
class BOMService
{
    private $db;

    public function getBOM(string $itemCode): array
    {
        $sql = "SELECT bom.*, sm.units, sm.item_description
                FROM 0_bom bom
                JOIN 0_stock_master sm ON bom.component = sm.stock_id
                WHERE bom.parent = " . $this->db->quote($itemCode) . "
                  AND bom.enabled = 1";
        return $this->db->fetchAll($sql);
    }

    public function isPhantom(string $itemCode): bool
    {
        $sql = "SELECT is_phantom FROM 0_stock_master WHERE stock_id = " . $this->db->quote($itemCode);
        return (bool)$this->db->fetchScalar($sql);
    }

    public function getLeadTime(string $itemCode): int
    {
        $sql = "SELECT lead_time FROM 0_stock_master WHERE stock_id = " . $this->db->quote($itemCode);
        return (int)$this->db->fetchScalar($sql);
    }
}
```

### 4.2 FA Stock Integration

Reads inventory levels from `0_stock_master`:

```php
class StockService
{
    private $db;

    public function getOnHand(string $itemCode): float
    {
        $sql = "SELECT quantity_on_hand FROM 0_stock_master WHERE stock_id = " . $this->db->quote($itemCode);
        return (float)$this->db->fetchScalar($sql);
    }

    public function getOnOrder(string $itemCode): float
    {
        $sql = "SELECT SUM(quantity_ordered - quantity_received) 
                FROM 0_purch_order_details 
                WHERE item_code = " . $this->db->quote($itemCode) . "
                  AND quantity_ordered > quantity_received";
        return (float)$this->db->fetchScalar($sql);
    }

    public function getSafetyStock(string $itemCode): float
    {
        $sql = "SELECT safety_stock FROM 0_stock_master WHERE stock_id = " . $this->db->quote($itemCode);
        return (float)$this->db->fetchScalar($sql);
    }
}
```

### 4.3 FA Purchasing Integration

Reads open purchase orders from `0_purch_order_details` and `0_purch_orders`:

```php
class PurchasingService
{
    private $db;

    public function getOpenPOs(string $itemCode): array
    {
        $sql = "SELECT pod.*, po.ord_date, po.supplier_id
                FROM 0_purch_order_details pod
                JOIN 0_purch_orders po ON pod.order_no = po.order_no
                WHERE pod.item_code = " . $this->db->quote($itemCode) . "
                  AND pod.quantity_ordered > pod.quantity_received
                  AND po.ord_status = 1";
        return $this->db->fetchAll($sql);
    }

    public function createPurchaseRequisition(string $itemCode, float $qty, string $dueDate): int
    {
        $sql = "INSERT INTO 0_purch_requisitions (item_code, quantity, requested_date, status)
                VALUES (" . $this->db->quote($itemCode) . ", " . $qty . ", " . $this->db->quote($dueDate) . ", 'pending')";
        return $this->db->executeUpdate($sql);
    }
}
```

---

## 5. BOM Explosion Algorithm

### 5.1 Explosion Process

```php
class BOMExploder
{
    private $db;
    private $bomService;
    private $stockService;
    private $phantomDissolve;

    public function __construct(DbConnectionInterface $db, BOMService $bomService, StockService $stockService)
    {
        $this->db = $db;
        $this->bomService = $bomService;
        $this->stockService = $stockService;
        $this->phantomDissolve = true;
    }

    public function explode(array $demands): array
    {
        $explodedRequirements = [];

        foreach ($demands as $demand) {
            $this->explodeItem($demand['item_code'], $demand['quantity'], $demand['due_date'], 1, $explodedRequirements);
        }

        return $this->aggregateRequirements($explodedRequirements);
    }

    private function explodeItem(string $itemCode, float $quantity, string $dueDate, int $level, array &$requirements): void
    {
        if ($level > 20) {
            return;
        }

        $bomItems = $this->bomService->getBOM($itemCode);

        foreach ($bomItems as $bomItem) {
            $componentQty = $bomItem['quantity'] * $quantity;

            if ($this->bomService->isPhantom($bomItem['component'])) {
                $this->explodeItem($bomItem['component'], $componentQty, $dueDate, $level + 1, $requirements);
                continue;
            }

            $requirements[] = [
                'item_code' => $bomItem['component'],
                'gross_qty' => $componentQty,
                'due_date' => $dueDate,
                'level' => $level,
                'parent_item' => $itemCode
            ];
        }
    }

    private function aggregateRequirements(array $requirements): array
    {
        $aggregated = [];

        foreach ($requirements as $req) {
            $key = $req['item_code'] . '|' . $req['due_date'];
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = $req;
            } else {
                $aggregated[$key]['gross_qty'] += $req['gross_qty'];
            }
        }

        return array_values($aggregated);
    }
}
```

### 5.2 Multi-Level Explosion Example

```
Product A (1) ─┬─► Component B (2) ─┬─► Component D (3)
               │                    └─► Component E (3)
               └─► Component C (2)

Gross Requirements for A:
  - B: 2 units
  - C: 1 unit
  - D: 2 × 2 = 4 units
  - E: 2 × 2 = 4 units
```

---

## 6. Net Requirements Calculation

### 6.1 Net Requirements Formula

```
Net Requirement = Gross Requirement - On-Hand - On-Order - Scheduled Receipts + Safety Stock
```

### 6.2 Implementation

```php
class NetRequirementsCalculator
{
    private $db;
    private $stockService;

    public function calculate(array $explodedRequirements): array
    {
        $netRequirements = [];

        foreach ($explodedRequirements as $req) {
            $onHand = $this->stockService->getOnHand($req['item_code']);
            $onOrder = $this->stockService->getOnOrder($req['item_code']);
            $safetyStock = $this->stockService->getSafetyStock($req['item_code']);

            $projectedAvailable = $onHand + $onOrder - $safetyStock;
            $netQty = $req['gross_qty'] - $projectedAvailable;

            $netRequirements[] = [
                'item_code' => $req['item_code'],
                'gross_qty' => $req['gross_qty'],
                'on_hand' => $onHand,
                'on_order' => $onOrder,
                'safety_stock' => $safetyStock,
                'projected_available' => $projectedAvailable,
                'net_qty' => max(0, $netQty),
                'due_date' => $req['due_date']
            ];
        }

        return $netRequirements;
    }
}
```

---

## 7. Lot Sizing Methods

### 7.1 Supported Methods

| Method | Code | Description |
|--------|------|-------------|
| Lot-for-Lot | LFL | Order exactly what is needed |
| Economic Order Quantity | EOQ | Minimize total holding + ordering costs |
| Fixed Order Quantity | FOQ | Fixed lot size, multiple orders if needed |
| Period Order Quantity | POQ | Fixed period, aggregate orders |

### 7.2 Implementation

```php
class LotSizingService
{
    private $db;
    private $stockService;

    public function applyLotSizing(string $method, array $netRequirements): array
    {
        switch ($method) {
            case 'LFL':
                return $this->lotForLot($netRequirements);
            case 'EOQ':
                return $this->economicOrderQty($netRequirements);
            case 'FOQ':
                return $this->fixedOrderQty($netRequirements);
            case 'POQ':
                return $this->periodOrderQty($netRequirements);
            default:
                return $netRequirements;
        }
    }

    private function lotForLot(array $netRequirements): array
    {
        foreach ($netRequirements as &$req) {
            $req['order_qty'] = $req['net_qty'];
        }
        return $netRequirements;
    }

    private function economicOrderQty(array $netRequirements): array
    {
        foreach ($netRequirements as &$req) {
            $annualDemand = $req['net_qty'] * 52;
            $orderingCost = $this->getOrderingCost($req['item_code']);
            $holdingCostRate = $this->getHoldingCostRate($req['item_code']);
            $unitCost = $this->getUnitCost($req['item_code']);

            $holdingCost = $unitCost * $holdingCostRate;
            if ($holdingCost > 0) {
                $eoq = sqrt((2 * $annualDemand * $orderingCost) / $holdingCost);
                $req['order_qty'] = ceil($eoq);
            } else {
                $req['order_qty'] = $req['net_qty'];
            }
        }
        return $netRequirements;
    }

    private function fixedOrderQty(array $netRequirements): array
    {
        $foq = $this->getFixedOrderQuantity();
        foreach ($netRequirements as &$req) {
            $req['order_qty'] = ceil($req['net_qty'] / $foq) * $foq;
        }
        return $netRequirements;
    }

    private function periodOrderQty(array $netRequirements): array
    {
        $periodWeeks = $this->getPeriodWeeks();
        $grouped = [];

        foreach ($netRequirements as $req) {
            $weekNum = date('W', strtotime($req['due_date']));
            $groupKey = floor($weekNum / $periodWeeks) * $periodWeeks;
            $key = $req['item_code'] . '|' . $groupKey;

            if (!isset($grouped[$key])) {
                $grouped[$key] = $req;
                $grouped[$key]['grouped_due_date'] = $req['due_date'];
            } else {
                $grouped[$key]['order_qty'] += $req['net_qty'];
            }
        }

        return array_values($grouped);
    }
}
```

---

## 8. Action Messages

### 8.1 Message Types

| Type | Description | Priority |
|------|-------------|----------|
| ORDER | Create new order | High |
| RESCHEDULE | Change order date | Medium |
| CANCEL | Cancel existing order | High |
| EXPEDITE | Speed up order | High |
| DEFER | Postpone order | Low |

### 8.2 Implementation

```php
class ActionMessageGenerator
{
    private $db;

    public function generate(array $plannedOrders, array $existingOrders): array
    {
        $messages = [];

        foreach ($plannedOrders as $planned) {
            $existing = $this->findExistingOrder($planned, $existingOrders);

            if ($existing === null) {
                $messages[] = $this->createOrderMessage($planned);
            } elseif ($this->needsReschedule($planned, $existing)) {
                $messages[] = $this->createRescheduleMessage($planned, $existing);
            }
        }

        foreach ($existingOrders as $existing) {
            if (!$this->hasPlannedOrder($existing, $plannedOrders)) {
                $messages[] = $this->createCancelMessage($existing);
            }
        }

        return $this->prioritizeMessages($messages);
    }

    private function createOrderMessage(array $planned): array
    {
        return [
            'item_code' => $planned['item_code'],
            'action_type' => 'ORDER',
            'new_date' => $planned['due_date'],
            'new_quantity' => $planned['order_qty'],
            'priority' => 1
        ];
    }

    private function createRescheduleMessage(array $planned, array $existing): array
    {
        return [
            'item_code' => $planned['item_code'],
            'action_type' => 'RESCHEDULE',
            'old_date' => $existing['due_date'],
            'new_date' => $planned['due_date'],
            'old_quantity' => $existing['order_qty'],
            'new_quantity' => $planned['order_qty'],
            'priority' => 2
        ];
    }

    private function createCancelMessage(array $existing): array
    {
        return [
            'item_code' => $existing['item_code'],
            'action_type' => 'CANCEL',
            'old_date' => $existing['due_date'],
            'old_quantity' => $existing['order_qty'],
            'priority' => 1
        ];
    }

    private function prioritizeMessages(array $messages): array
    {
        usort($messages, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        return $messages;
    }
}
```

---

## 9. File Structure

```
ksf_FA_MRP/
├── ksf_FA_MRP.php
├── hooks.php
├── sql/
│   └── install.sql
├── includes/
│   ├── mrp_db.inc
│   ├── MRPEngine.php
│   ├── BOMExploder.php
│   ├── NetRequirementsCalculator.php
│   ├── LotSizingService.php
│   ├── ActionMessageGenerator.php
│   └── services/
│       ├── BOMService.php
│       ├── StockService.php
│       └── PurchasingService.php
└── ProjectDcs/
    ├── ARCH/
    │   └── ARCH-MRP-001-mrp-system.md
    ├── BR/
    │   └── BR-MRP-001-mrp-system.md
    ├── FR/
    │   ├── FR-MRP-001-001.md
    │   ├── FR-MRP-001-002.md
    │   ├── FR-MRP-001-003.md
    │   ├── FR-MRP-001-004.md
    │   ├── FR-MRP-001-005.md
    │   ├── FR-MRP-001-006.md
    │   └── FR-MRP-001-007.md
    ├── UT/
    │   └── UT-MRP-001-001-001.md
    └── UAT/
        └── UAT-MRP-001-mrp-system.md
```

---

## 10. Related Documents

| Document | Description |
|----------|-------------|
| BR-MRP-001 | Business Requirements |
| FR-MRP-001-001 | MRP Parameters |
| FR-MRP-001-002 | Demand Aggregation |
| FR-MRP-001-003 | BOM Explosion |
| FR-MRP-001-004 | Net Requirements |
| FR-MRP-001-005 | Lot Sizing |
| FR-MRP-001-006 | Planned Orders |
| FR-MRP-001-007 | Action Messages |
