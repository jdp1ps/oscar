<?php


namespace Oscar\Formatter\Utils;


use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpreadsheetStyleUtils
{
    private $colorBlueEntete;
    private $colorWhite;
    private $colorBlack;
    private $colorGreyDark;
    private $colorGreyMedium;
    private $colorGreyLight;
    private $colorGreyUltraLight;
    private $colorBlueLightEntete;
    private $colorBlueWhiteEntete;

    private $colorNoValue;


    private $colorResearch = '71bdae';
    private $colorResearchBG = 'ebf8f5';
    private $colorEducation = 'c2e0ae';
    private $colorEducationBG = 'ecf6e5';
    private $colorAbs = 'f8aa4a';
    private $colorAbsBG = 'faefea';
    private $colorOther = 'd1d6a5';
    private $colorOtherBG = 'f8faea';

    private $baseFontSize;

    /**
     * @return SpreadsheetStyleUtils
     */
    public static function getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct()
    {
        // COULEURS
        $this->colorBlueEntete = self::color('#537992');
        $this->colorWhite = self::color('#FFFFFF');
        $this->colorBlack = self::color('#000000');
        $this->colorGreyDark = self::color('#333333');
        $this->colorGreyMedium = self::color('#555555');
        $this->colorGreyLight = self::color('#d7dbce');
        $this->colorGreyUltraLight = self::color('#fefefe');
        $this->colorBlueLightEntete = self::color('#dcedf9');
        $this->colorBlueWhiteEntete = self::color('#dde6eb');
        $this->colorNoValue = self::color('#808080');

        $this->colorResearch = self::color('#71bdae');
        $this->colorResearchBG = self::color('#ebf8f5');
        $this->colorEducation = self::color('#c2e0ae');
        $this->colorEducationBG = self::color('#ecf6e5');
        $this->colorAbs = self::color('#f8aa4a');
        $this->colorAbsBG = self::color('#faefea');
        $this->colorOther = self::color('#d1d6a5');
        $this->colorOtherBG = self::color('#f8faea');

        $this->baseFontSize = 10;
    }


    private static function color($colorhex)
    {
        return "FF" . substr($colorhex, 1);
    }


    public function getEntete()
    {
        static $entete;
        if ($entete === null) {
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

    protected function fillSolidCellFormat($color, $fontSize, $bgColor, $hAlign = Alignment::HORIZONTAL_CENTER, $vAlign = Alignment::VERTICAL_CENTER, $bold = false)
    {
        return [
            'font' => [
                'bold' => $bold,
                'color' => [
                    'argb' => $color,
                ],
                'size' => $fontSize
            ],
            'alignment' => [
                'horizontal' => $hAlign,
                'vertical' => $vAlign,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $bgColor]],
        ];
    }

    public function getLabelTitle()
    {
        static $labelTitle;
        if ($labelTitle === null) {
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
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->colorBlueWhiteEntete]],
            ];
        }
        return $labelTitle;
    }

    public function getLabelValue()
    {
        static $labelValue;
        if ($labelValue === null) {
            $labelValue = [
                'font' => [
                    'bold' => false,
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
        if ($total === null) {
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

    protected function getBordersThin($color = null, $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
    {
        if ($color === null) {
            $color = $this->colorGreyLight;
        }
        return
            [
                'top' => ['borderStyle' => $borderStyle, 'color' => ['argb' => $color]],
                'right' => ['borderStyle' => $borderStyle, 'color' => ['argb' => $color]],
                'bottom' => ['borderStyle' => $borderStyle, 'color' => ['argb' => $color]],
                'left' => ['borderStyle' => $borderStyle, 'color' => ['argb' => $color]],
            ];
    }

    public function withValue()
    {
        return ['font' => ['bold' => false, 'size' => $this->baseFontSize - 1],
            'borders' => $this->getBordersThin(),
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER,],
        ];
    }

    public function noValue()
    {
        return [
            'font' => [
                'bold' => false,
                'size' => $this->baseFontSize - 1,
                'color' => ['argb' => $this->colorNoValue]],
            'borders' => $this->getBordersThin(),
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
    }

    public function cellTotalBottom()
    {
        return [
            'font' => [
                'bold' => true, '
                size' => $this->baseFontSize + 1],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'ff000000']]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ];
    }

    public function comment()
    {
        return ['font' => ['bold' => false, 'size' => $this->baseFontSize - 1, 'color' => ['argb' => $this->colorGreyDark]],
            'borders' => $this->getBordersThin(),
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP
            ]
        ];
    }

    public function personComment()
    {
        return ['font' => ['bold' => false, 'size' => $this->baseFontSize - 1, 'color' => ['argb' => $this->colorGreyMedium]],
            'borders' => $this->getBordersThin(),
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_TOP
            ]
        ];
    }

    public function person()
    {
        return [
            'font' => [
                'bold' => true,
                'size' => $this->baseFontSize,
                'color' => [
                    'argb' => $this->colorGreyDark,
                ],
            ],
            'borders' => $this->getBordersThin(),
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => $this->colorGreyUltraLight]
            ],
        ];
    }

    public function totalColumn()
    {
        return [
            'font' => [
                'bold' => true,
                'size' => $this->baseFontSize,
            ],
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $this->colorBlack]],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $this->colorGreyLight]],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $this->colorGreyLight]],
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $this->colorGreyLight]],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->colorWhite]],
        ];
    }

    protected function headDomain($color, $bold = true, $hAlign = Alignment::HORIZONTAL_CENTER, $vAlign = Alignment::VERTICAL_CENTER)
    {
        return [
            'font' => [
                'bold' => $bold,
                'size' => $this->baseFontSize
            ],
            'alignment' => [
                'horizontal' => $hAlign,
                'vertical' => $vAlign,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => $color
                ]
            ]
        ];
    }

    public function headResearch()
    {
        return $this->headDomain($this->colorResearch);
    }

    public function headAbs()
    {
        return $this->headDomain($this->colorAbs);
    }

    public function headEducation()
    {
        return $this->headDomain($this->colorEducation);
    }

    public function headOther()
    {
        return $this->headDomain($this->colorOther);
    }


}