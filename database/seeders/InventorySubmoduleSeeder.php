<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\ApprovalRequest;
use Illuminate\Database\Seeder;

class InventorySubmoduleSeeder extends Seeder
{
    public function run(): void
    {
        // 15 Realistic Sample Rows matching the original structure
        $items = [
            ['id' => 'PC-001', 'name' => 'AMD Ryzen 7 7800X3D CPU', 'category' => 'Processor', 'currentQty' => 22, 'minLimit' => 10, 'maxLimit' => 50],
            ['id' => 'PC-002', 'name' => 'NVIDIA RTX 4070 Super 12GB GPU', 'category' => 'Graphics Card', 'currentQty' => 3, 'minLimit' => 5, 'maxLimit' => 25],
            ['id' => 'PC-003', 'name' => 'Corsair Vengeance 32GB DDR5 RAM', 'category' => 'Memory', 'currentQty' => 40, 'minLimit' => 15, 'maxLimit' => 60],
            ['id' => 'PC-004', 'name' => 'Samsung 990 Pro 2TB NVMe SSD', 'category' => 'Storage', 'currentQty' => 0, 'minLimit' => 8, 'maxLimit' => 40],
            ['id' => 'PC-005', 'name' => 'ASUS ROG Strix B650-A Motherboard', 'category' => 'Motherboard', 'currentQty' => 12, 'minLimit' => 5, 'maxLimit' => 30],
            ['id' => 'PC-006', 'name' => 'NZXT H6 Flow Mid-Tower Case', 'category' => 'PC Case', 'currentQty' => 52, 'minLimit' => 10, 'maxLimit' => 50],
            ['id' => 'PC-007', 'name' => 'Corsair RM850x 850W Gold PSU', 'category' => 'Power Supply', 'currentQty' => 18, 'minLimit' => 6, 'maxLimit' => 35],
            ['id' => 'PC-008', 'name' => 'Lian Li Galahad II Trinity AIO Cooler', 'category' => 'Cooling', 'currentQty' => 4, 'minLimit' => 5, 'maxLimit' => 20],
            ['id' => 'PC-009', 'name' => 'Noctua NF-A12x25 120mm Case Fan', 'category' => 'Cooling', 'currentQty' => 55, 'minLimit' => 20, 'maxLimit' => 100],
            ['id' => 'PC-010', 'name' => 'Thermal Grizzly Kryonaut Paste (1g)', 'category' => 'Accessories', 'currentQty' => 32, 'minLimit' => 10, 'maxLimit' => 50],
            ['id' => 'PC-011', 'name' => 'Logitech G Pro X Superlight Mouse', 'category' => 'Peripherals', 'currentQty' => 25, 'minLimit' => 5, 'maxLimit' => 30],
            ['id' => 'PC-012', 'name' => 'Wooting 60HE Hall Effect Keyboard', 'category' => 'Peripherals', 'currentQty' => 2, 'minLimit' => 5, 'maxLimit' => 15],
            ['id' => 'PC-013', 'name' => 'Elgato Stream Deck MK.2', 'category' => 'Streaming', 'currentQty' => 15, 'minLimit' => 5, 'maxLimit' => 20],
            ['id' => 'PC-014', 'name' => 'Audio-Technica ATH-M50x Headphones', 'category' => 'Audio', 'currentQty' => 8, 'minLimit' => 10, 'maxLimit' => 40],
            ['id' => 'PC-015', 'name' => 'Shure SM7B Vocal Microphone', 'category' => 'Audio', 'currentQty' => 0, 'minLimit' => 3, 'maxLimit' => 10],
        ];

        foreach ($items as $item) {
            InventoryItem::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}