<?php

namespace App\Exports;

use App\Models\Belanja;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class RekapExport implements 
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting
{
    protected $userId;
    protected $tahunAnggaran;

    public function __construct($userId, $year = null, $month = null, $kategori = null, $tahunAnggaran = null)
    {
        $this->userId = $userId;
        $this->tahunAnggaran = $tahunAnggaran;
    }


    public function collection()
    {

        $query = Belanja::where('user_id', $this->userId);


        if($this->tahunAnggaran){

            $query->where(
                'tahun_anggaran',
                $this->tahunAnggaran
            );

        }


        if(request('bulan')){

            $query->whereMonth(
                'tanggal',
                request('bulan')
            );

        }


        if(request('kategori')){

            $query->where(
                'kategori',
                request('kategori')
            );

        }


        return $query
            ->orderBy(
                'tanggal',
                'asc'
            )
            ->get([
                'tanggal',
                'uraian',
                'kategori',
                'total'
            ]);

    }




    public function headings(): array
    {

        return [

            'Tanggal',
            'Uraian',
            'Kategori',
            'Total'

        ];

    }




    public function styles(Worksheet $sheet)
    {


        // Judul laporan

        $sheet->insertNewRowBefore(1,3);


        $sheet->setCellValue(
            'A1',
            'REKAP KEUANGAN SILPJ'
        );


        $sheet->setCellValue(
            'A2',
            'SDN 007 SABANG SUBIK'
        );


        $sheet->setCellValue(
            'A3',
            'Laporan Penggunaan Anggaran'
        );



        // Gabung judul

        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');



        // Header tabel

        $sheet
->getStyle('A4:D4')
->applyFromArray([

            'font'=>[

                'bold'=>true,
                'color'=>[
                    'rgb'=>'FFFFFF'
                ]

            ],


            'fill'=>[

                'fillType'=>'solid',
                'startColor'=>[
                    'rgb'=>'2563EB'
                ]

            ],


            'alignment'=>[

                'horizontal'=>'center'

            ]

        ]);




        // Judul

        $sheet
        ->getStyle('A1')
        ->applyFromArray([

            'font'=>[
                'bold'=>true,
                'size'=>16
            ]

        ]);



        $sheet
        ->getStyle('A2:A3')
        ->applyFromArray([

            'font'=>[
                'bold'=>true
            ]

        ]);




        // Border tabel

        $sheet
->getStyle('A4:D200')
->applyFromArray([

            'borders'=>[

                'allBorders'=>[

                    'borderStyle'=>'thin'

                ]

            ]

        ]);



        // Freeze header

       $sheet->freezePane('A5');



        // Auto ukuran

        foreach(range('A','D') as $col){

            $sheet
            ->getColumnDimension($col)
            ->setAutoSize(true);

        }



        return $sheet;

    }





    public function columnFormats(): array
    {

        return [

            'D'=>NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1

        ];

    }



}