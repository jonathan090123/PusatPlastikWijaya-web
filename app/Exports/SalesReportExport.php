<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        protected string $period,
        protected $startDate,
        protected $endDate
    ) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->period, $this->startDate, $this->endDate),
            new BestSellingSheet($this->startDate, $this->endDate),
            new TopCustomersSheet($this->startDate, $this->endDate),
            new OrderListSheet($this->startDate, $this->endDate),
        ];
    }
}

/* ─────────────────────────── SHEET 1: Ringkasan ─────────────────────────── */

class SummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        protected string $period,
        protected $startDate,
        protected $endDate
    ) {}

    public function title(): string { return 'Ringkasan'; }

    public function headings(): array
    {
        return ['Metrik', 'Nilai'];
    }

    public function collection()
    {
        $completedQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        $allOrdersQuery = Order::whereBetween('created_at', [$this->startDate, $this->endDate]);

        $totalRevenue    = (clone $completedQuery)->sum('total');
        $totalOrders     = (clone $allOrdersQuery)->count();
        $completedOrders = (clone $completedQuery)->count();
        $cancelledOrders = (clone $allOrdersQuery)->where('status', 'cancelled')->count();
        $totalItemsSold  = OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'completed')
              ->whereBetween('created_at', [$this->startDate, $this->endDate]);
        })->sum('quantity');
        $avgOrderValue   = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        $periodLabel = match ($this->period) {
            'today'  => 'Hari Ini',
            'week'   => 'Minggu Ini',
            'month'  => 'Bulan Ini',
            'year'   => 'Tahun Ini',
            'custom' => 'Kustom',
            default  => 'Bulan Ini',
        };

        return collect([
            ['Periode',                    $periodLabel],
            ['Tanggal Mulai',              Carbon::parse($this->startDate)->format('d M Y')],
            ['Tanggal Selesai',            Carbon::parse($this->endDate)->format('d M Y')],
            [''],
            ['Total Pendapatan (Rp)',       number_format($totalRevenue, 0, ',', '.')],
            ['Total Pesanan',               number_format($totalOrders)],
            ['Pesanan Selesai',             number_format($completedOrders)],
            ['Pesanan Dibatalkan',          number_format($cancelledOrders)],
            ['Item Terjual',               number_format($totalItemsSold)],
            ['Rata-rata per Pesanan (Rp)', number_format($avgOrderValue, 0, ',', '.')],
        ]);
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 28];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert title rows above heading
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'LAPORAN PENJUALAN - PUSAT PLASTIK WIJAYA');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1e3a8a']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A2:B2');
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d M Y H:i'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'color' => ['rgb' => '6b7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Header row (now row 3)
                $sheet->getStyle('A3:B3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Highlight metric rows
                for ($row = 8; $row <= 13; $row++) {
                    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'eff6ff']],
                    ]);
                }

                // Borders
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A3:B{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'd1d5db']]],
                ]);
            },
        ];
    }
}

/* ─────────────────────────── SHEET 2: Produk Terlaris ───────────────────── */

class BestSellingSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected $startDate, protected $endDate) {}

    public function title(): string { return 'Produk Terlaris'; }

    public function headings(): array
    {
        return ['#', 'Nama Produk', 'Qty Terjual', 'Pendapatan (Rp)'];
    }

    public function collection()
    {
        return OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'completed')
              ->whereBetween('created_at', [$this->startDate, $this->endDate]);
        })
        ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
        ->groupBy('product_name')
        ->orderByDesc('total_qty')
        ->get()
        ->map(function ($item, $i) {
            return [
                $i + 1,
                $item->product_name,
                (int) $item->total_qty,
                number_format($item->total_revenue, 0, ',', '.'),
            ];
        });
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 40, 'C' => 16, 'D' => 22];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065f46']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:D1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Highlight top 3
                $topColors = ['fef9c3', 'f1f5f9', 'fff7ed'];
                foreach ([2, 3, 4] as $i => $row) {
                    if ($row <= $lastRow) {
                        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $topColors[$i]]],
                        ]);
                    }
                }

                $sheet->getStyle("A1:D{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'd1d5db']]],
                ]);
            },
        ];
    }
}

/* ─────────────────────────── SHEET 3: Pelanggan Teratas ─────────────────── */

class TopCustomersSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected $startDate, protected $endDate) {}

    public function title(): string { return 'Pelanggan Teratas'; }

    public function headings(): array
    {
        return ['#', 'Nama Pelanggan', 'Email', 'Jumlah Pesanan', 'Total Belanja (Rp)'];
    }

    public function collection()
    {
        return Order::where('status', 'completed')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select('user_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->with('user')
            ->get()
            ->map(function ($row, $i) {
                return [
                    $i + 1,
                    $row->user->name  ?? '-',
                    $row->user->email ?? '-',
                    $row->total_orders,
                    number_format($row->total_spent, 0, ',', '.'),
                ];
            });
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 30, 'C' => 34, 'D' => 18, 'E' => 22];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:E1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("A1:E{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'd1d5db']]],
                ]);
            },
        ];
    }
}

/* ─────────────────────────── SHEET 4: Daftar Pesanan ────────────────────── */

class OrderListSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected $startDate, protected $endDate) {}

    public function title(): string { return 'Daftar Pesanan'; }

    public function headings(): array
    {
        return ['No. Invoice', 'Pelanggan', 'Email', 'Status', 'Total (Rp)', 'Tanggal', 'Metode Bayar'];
    }

    public function collection()
    {
        return Order::with('user', 'payment')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $statusLabel = match ($order->status) {
                    'pending'    => 'Menunggu Pembayaran',
                    'processing' => 'Diproses',
                    'shipped'    => 'Dikirim',
                    'completed'  => 'Selesai',
                    'cancelled'  => 'Dibatalkan',
                    'expired'    => 'Kadaluarsa',
                    default      => ucfirst($order->status),
                };
                return [
                    $order->invoice_number,
                    $order->user->name  ?? '-',
                    $order->user->email ?? '-',
                    $statusLabel,
                    number_format($order->total, 0, ',', '.'),
                    Carbon::parse($order->created_at)->format('d M Y H:i'),
                    $order->payment->payment_type ?? '-',
                ];
            });
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 28, 'C' => 34, 'D' => 22, 'E' => 18, 'F' => 20, 'G' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:G1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Warna status kolom D
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell("D{$row}")->getValue();
                    $color  = match ($status) {
                        'Selesai'                    => 'd1fae5',
                        'Dibatalkan', 'Kadaluarsa'   => 'fee2e2',
                        'Dikirim'                    => 'dbeafe',
                        'Diproses'                   => 'fef9c3',
                        default                      => 'ffffff',
                    };
                    $sheet->getStyle("D{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                    ]);
                }

                $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'd1d5db']]],
                ]);

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
