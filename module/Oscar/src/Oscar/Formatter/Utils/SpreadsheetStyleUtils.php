<?php


namespace Oscar\Formatter\Utils;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpreadsheetStyleUtils
{
    private $colorBlueEntete;
    private $colorWhite;
    private $colorGreyMedium;
    private $colorBlueLightEntete;
    private $colorBlueWhiteEntete;

    /**
     * @return SpreadsheetStyleUtils
     */
    public static function getInstance()
    {
        static $instance;
        if( $instance === null ){
            $instance = new self();
        }
        return $instance;
    }

    private function __construct()
    {
        // COULEURS
        $this->colorBlueEntete = self::color('#537992');
        $this->colorWhite = self::color('#FFFFFF');
        $this->colorGreyMedium = self::color('#555555');
        $this->colorBlueLightEntete = self::color('#dcedf9');
        $this->colorBlueWhiteEntete = self::color('#dde6eb');
    }



    private static function color($colorhex){
        return "FF".substr($colorhex, 1);
    }

    protected function fillSolidCellFormat($color, $fontSize, $bgColor, $hAlign=Alignment::HORIZONTAL_CENTER, $vAlign=Alignment::VERTICAL_CENTER, $bold=false)
    {
        return [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => $this->colorWhite,
                ],
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->colorBlueWhiteEntete]],
        ];
    }

    public function getEntete(){
        static $entete;
        if( $entete === null ){
            $entete = [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => $this->colorBlueEntete,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startColor' => [
                        'argb' => $this->colorBlueLightEntete,
                    ],
                    'endColor' => [
                        'argb' => $this->colorBlueWhiteEntete,
                    ],
                ],
            ];
        }
        return $entete;
    }

    public function getLabelTitle(){
        static $labelTitle;
        if( $labelTitle === null ){
            $labelTitle = [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => $this->colorGreyMedium,
                    ],
                    'size' => 10
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [ 'fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->colorBlueWhiteEntete ]],
            ];
        }
        return $labelTitle;
    }

    public function getLabelValue()
    {
        static $labelValue;
        if( $labelValue === null ) {
            $labelValue = [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => $this->colorWhite,
                    ],
                    'size' => 10
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->colorBlueWhiteEntete]],
            ];
        }
        return $labelValue;
    }

    public function getTotal()
    {
        static $total;
        if( $total === null ) {
            $total = [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => $this->colorBlueEntete,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ];
        }
        return $total;
    }

    public function foo()
    {
        static $headResearch;
        if( !$headResearch ){
            $headResearch = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
                'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                'fill' => [ 'fillType' => Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorResearch" ]],];
        }

    }

/******

    function foo(){



        $total = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FF537992',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];

        $this->addStyle("total", $total);


        $colorResearch      = '71bdae'; $colorResearchBG    = 'ebf8f5';
        $colorEducation     = 'c2e0ae'; $colorEducationBG   = 'ecf6e5';
        $colorAbs           = 'f8aa4a'; $colorAbsBG         = 'faefea';
        $colorOther         = 'd1d6a5'; $colorOtherBG       = 'f8faea';

        $baseFontSize = 10;

        $headResearch = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorResearch" ]],];
        $this->addStyle("headResearch", $headResearch);

        $headAbs = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorAbs" ]],];
        $this->addStyle("headAbs", $headAbs);

        $headEducation = [ 'font' => [ 'bold' => true,'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorEducation"  ]],];
        $this->addStyle("headEducation", $headEducation);

        $headOther = [ 'font' => [ 'bold' => true,'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorOther" ]],];
        $this->addStyle("headOther", $headOther);

        $withValue = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize-1 ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
        ];
        $this->addStyle("withValue", $withValue);

        $cellTotalBottom = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize+1 ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ff000000'] ]
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
        ];
        $this->addStyle("cellTotalBottom", $cellTotalBottom);

        $noValue = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff808080' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]];
        $this->addStyle("noValue", $noValue);


        $comment = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff333333' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,]];
        $this->addStyle("comment", $comment);

        $personComment = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff555555' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,]];
        $this->addStyle("personComment", $personComment);

        $person = [
            'font' => [
                'bold' => true,
                'size' => $baseFontSize,
                'color' => [
                    'argb' => 'FF333333',
                ],
            ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'fffefefe' ]
            ],

        ];
        $this->addStyle("person", $person);


        $totalColumn = [
            'font' => [
                'bold' => true,
                'size' => $baseFontSize,
            ],
            'borders' => [
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ff000000'] ],
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffffffff" ]],
        ];
        $this->addStyle("totalColumn", $totalColumn);
    }
    /**********/
}