<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Services;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Export extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(): string
    {
        $end   = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-30 days'));

        return view('admin/export/index', [
            'title'      => 'Xuất Excel',
            'navActive'  => 'export',
            'defaultStart' => $start,
            'defaultEnd'   => $end,
        ]);
    }

    public function download()
    {
        $type  = (string) $this->request->getGet('type');
        $start = (string) $this->request->getGet('start_date');
        $end   = (string) $this->request->getGet('end_date');

        if (! in_array($type, ['orders', 'imports', 'customers'], true)) {
            return redirect()->to(site_url('admin/export'))->with('error', 'Loại xuất không hợp lệ.');
        }

        try {
            $spreadsheet = Services::exportSpreadsheetService()->generate($type, $start, $end);
        } catch (\InvalidArgumentException $e) {
            return redirect()->to(site_url('admin/export'))->with('error', $e->getMessage());
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        try {
            $writer->save('php://output');
        } catch (\Throwable $e) {
            ob_end_clean();

            return redirect()->to(site_url('admin/export'))->with(
                'error',
                'Không ghi được file Excel. Bật extension php_zip (ext-zip) trong PHP nếu chưa có.',
            );
        }

        $binary = (string) ob_get_clean();
        $name   = 'export_' . $type . '_' . date('Ymd_His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody($binary);
    }
}
