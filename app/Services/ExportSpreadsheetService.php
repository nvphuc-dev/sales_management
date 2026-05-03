<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExportSpreadsheetService
{
    private const VND_FORMAT = '#,##0 "₫"';

    public function __construct(
        private readonly BaseConnection $db,
    ) {
    }

    public static function make(): self
    {
        return new self(Database::connect());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function generate(string $type, string $startYmd, string $endYmd): Spreadsheet
    {
        $this->assertDateRange($startYmd, $endYmd);

        return match ($type) {
            'orders'    => $this->buildOrders($startYmd, $endYmd),
            'imports'   => $this->buildImports($startYmd, $endYmd),
            'customers' => $this->buildCustomers($startYmd, $endYmd),
            default     => throw new \InvalidArgumentException('Loại xuất không hợp lệ.'),
        };
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertDateRange(string $startYmd, string $endYmd): void
    {
        $s = $this->parseYmd($startYmd);
        $e = $this->parseYmd($endYmd);
        if ($s === null || $e === null) {
            throw new \InvalidArgumentException('Ngày bắt đầu / kết thúc phải đúng định dạng YYYY-MM-DD.');
        }

        if ($s > $e) {
            throw new \InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
        }
    }

    private function parseYmd(string $v): ?\DateTimeImmutable
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $v);

        return $d instanceof \DateTimeImmutable && $d->format('Y-m-d') === $v ? $d : null;
    }

    private function buildOrders(string $startYmd, string $endYmd): Spreadsheet
    {
        $sql = <<<'SQL'
SELECT o.id AS order_id, o.order_code, o.created_at, o.status, o.payment_status, o.total_amount,
       c.name AS customer_name,
       p.name AS product_name, p.sku,
       oi.quantity, oi.unit_price, oi.line_total
FROM orders o
INNER JOIN customers c ON c.id = o.customer_id
INNER JOIN order_items oi ON oi.order_id = o.id
INNER JOIN products p ON p.id = oi.product_id
WHERE DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?
ORDER BY o.id ASC, oi.id ASC
SQL;
        $rows = $this->db->query($sql, [$startYmd, $endYmd])->getResultArray();

        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('DonHang');

        $headers = [
            'Mã đơn', 'Khách hàng', 'Ngày tạo', 'TT đơn', 'TT thanh toán', 'Tổng đơn',
            'Sản phẩm', 'SKU', 'SL', 'Đơn giá', 'Thành tiền',
        ];
        $this->writeHeaderRow($sh, $headers);

        $baseRow = 2;
        foreach ($rows as $i => $r) {
            $row = $baseRow + $i;
            $sh->setCellValue("A{$row}", $r['order_code']);
            $sh->setCellValue("B{$row}", $r['customer_name']);
            $sh->setCellValue("C{$row}", (string) $r['created_at']);
            $sh->setCellValue("D{$row}", $r['status']);
            $sh->setCellValue("E{$row}", $r['payment_status']);
            $sh->setCellValue("F{$row}", (float) $r['total_amount']);
            $sh->setCellValue("G{$row}", $r['product_name']);
            $sh->setCellValue("H{$row}", $r['sku']);
            $sh->setCellValue("I{$row}", (int) $r['quantity']);
            $sh->setCellValue("J{$row}", (float) $r['unit_price']);
            $sh->setCellValue("K{$row}", (float) $r['line_total']);
        }

        $last = max(1, $baseRow + count($rows) - 1);
        if ($last >= $baseRow) {
            $sh->getStyle("F{$baseRow}:F{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
            $sh->getStyle("J{$baseRow}:J{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
            $sh->getStyle("K{$baseRow}:K{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
        }

        $this->mergeGroupedHeaderColumns($sh, $rows, $baseRow, ['A', 'B', 'C', 'D', 'E', 'F'], 'order_id');

        return $ss;
    }

    private function buildImports(string $startYmd, string $endYmd): Spreadsheet
    {
        $sql = <<<'SQL'
SELECT io.id AS import_id, io.code, io.created_at, io.total_amount,
       s.name AS supplier_name,
       p.name AS product_name, p.sku,
       ii.quantity, ii.unit_price, ii.line_total
FROM import_orders io
INNER JOIN suppliers s ON s.id = io.supplier_id
INNER JOIN import_order_items ii ON ii.import_order_id = io.id
INNER JOIN products p ON p.id = ii.product_id
WHERE DATE(io.created_at) >= ? AND DATE(io.created_at) <= ?
ORDER BY io.id ASC, ii.id ASC
SQL;
        $rows = $this->db->query($sql, [$startYmd, $endYmd])->getResultArray();

        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('NhapHang');

        $headers = [
            'Mã phiếu', 'Nhà cung cấp', 'Ngày tạo', 'Tổng phiếu',
            'Sản phẩm', 'SKU', 'SL', 'Đơn giá', 'Thành tiền',
        ];
        $this->writeHeaderRow($sh, $headers);

        $baseRow = 2;
        foreach ($rows as $i => $r) {
            $row = $baseRow + $i;
            $sh->setCellValue("A{$row}", $r['code']);
            $sh->setCellValue("B{$row}", $r['supplier_name']);
            $sh->setCellValue("C{$row}", (string) $r['created_at']);
            $sh->setCellValue("D{$row}", (float) $r['total_amount']);
            $sh->setCellValue("E{$row}", $r['product_name']);
            $sh->setCellValue("F{$row}", $r['sku']);
            $sh->setCellValue("G{$row}", (int) $r['quantity']);
            $sh->setCellValue("H{$row}", (float) $r['unit_price']);
            $sh->setCellValue("I{$row}", (float) $r['line_total']);
        }

        $last = max(1, $baseRow + count($rows) - 1);
        if ($last >= $baseRow) {
            $sh->getStyle("D{$baseRow}:D{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
            $sh->getStyle("H{$baseRow}:H{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
            $sh->getStyle("I{$baseRow}:I{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
        }

        $this->mergeGroupedHeaderColumns($sh, $rows, $baseRow, ['A', 'B', 'C', 'D'], 'import_id');

        return $ss;
    }

    private function buildCustomers(string $startYmd, string $endYmd): Spreadsheet
    {
        $sql = <<<'SQL'
SELECT id, name, phone, email, address, total_purchase, total_paid, current_debt, created_at
FROM customers
WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
ORDER BY id ASC
SQL;
        $rows = $this->db->query($sql, [$startYmd, $endYmd])->getResultArray();

        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('KhachHang');

        $headers = [
            'ID', 'Tên', 'SĐT', 'Email', 'Địa chỉ', 'Tổng mua', 'Đã trả', 'Công nợ', 'Ngày tạo',
        ];
        $this->writeHeaderRow($sh, $headers);

        $baseRow = 2;
        foreach ($rows as $i => $r) {
            $row = $baseRow + $i;
            $sh->setCellValue("A{$row}", (int) $r['id']);
            $sh->setCellValue("B{$row}", $r['name']);
            $sh->setCellValue("C{$row}", (string) ($r['phone'] ?? ''));
            $sh->setCellValue("D{$row}", (string) ($r['email'] ?? ''));
            $sh->setCellValue("E{$row}", (string) ($r['address'] ?? ''));
            $sh->setCellValue("F{$row}", (float) $r['total_purchase']);
            $sh->setCellValue("G{$row}", (float) $r['total_paid']);
            $sh->setCellValue("H{$row}", (float) $r['current_debt']);
            $sh->setCellValue("I{$row}", (string) $r['created_at']);
        }

        $last = max(1, $baseRow + count($rows) - 1);
        if ($last >= $baseRow) {
            $sh->getStyle("F{$baseRow}:H{$last}")->getNumberFormat()->setFormatCode(self::VND_FORMAT);
        }

        return $ss;
    }

    /**
     * @param list<string>        $headers
     * @param list<string>        $mergeCols
     * @param list<array<string, mixed>> $rows
     */
    private function writeHeaderRow(Worksheet $sh, array $headers): void
    {
        $idx = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($idx);
            $sh->setCellValue("{$col}1", $h);
            $idx++;
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sh->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sh->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $mergeCols
     */
    private function mergeGroupedHeaderColumns(
        Worksheet $sh,
        array $rows,
        int $baseRow,
        array $mergeCols,
        string $groupKey,
    ): void {
        $n = count($rows);
        if ($n === 0) {
            return;
        }

        $i = 0;
        while ($i < $n) {
            $gid = $rows[$i][$groupKey] ?? null;
            $j   = $i + 1;
            while ($j < $n && ($rows[$j][$groupKey] ?? null) === $gid) {
                $j++;
            }

            $rowFrom = $baseRow + $i;
            $rowTo   = $baseRow + $j - 1;
            if ($rowTo > $rowFrom) {
                foreach ($mergeCols as $col) {
                    $sh->mergeCells("{$col}{$rowFrom}:{$col}{$rowTo}");
                    $sh->getStyle("{$col}{$rowFrom}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
            }

            $i = $j;
        }
    }
}
