<?php
namespace Oscar\Utils;

use Oscar\Exception\OscarException;

class PeriodInfos
{
    private int $month;
    private int $year;

    private string $_label='';
    private string $_code;

    /**
     * PeriodInfos constructor.
     * @param int $month
     * @param int $year
     */
    protected function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
        $this->_label = '';
        $this->_code = '';
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * Décrémente d'un mois.
     *
     * @return $this
     */
    public function prevMonth() :self
    {
        $this->month--;
        if( $this->month == 0 ){
            $this->month = 12;
            $this->year--;
        }
        $this->_label = '';
        return $this;
    }

    /**
     * Incremente d'un mois.
     *
     * @return $this
     */
    public function nextMonth() :self
    {
        $this->month++;
        if( $this->month > 12 ){
            $this->month = 1;
            $this->year++;
        }
        $this->_label = '';
        return $this;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    public function getPeriodLabel(): string
    {
        if (!$this->_label) {
            $fmt = datefmt_create(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                'Europe/Paris',
                \IntlDateFormatter::GREGORIAN,
                'MMMM yyyy'
            );
            $dateRef = new \DateTime(sprintf('%s-%s-01', $this->getYear(), $this->getMonth()));
            $this->_label = $fmt->format($dateRef);
        }
        return $this->_label;
    }

    public function getPeriodCode(): string
    {
        return sprintf('%s-%s', $this->getYear(), ($this->getMonth() < 10 ? '0' : '') . $this->getMonth());
    }

    public function toArray(): array
    {
        return [
            'period' => sprintf('%s-%s', $this->getYear(), $this->getMonth()),
            'periodLabel' => $this->getPeriodLabel(),
            'periodCode' => $this->getPeriodCode(),
            'month' => $this->getMonth(),
            'year' => $this->getYear()
        ];
    }

    public static function getPeriodInfosObj(string $str): PeriodInfos
    {
        $re = '/([0-9]{4})\-(10|11|12|0?[1-9])$/';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            $month = intval($matches[0][2]);
            $year = intval($matches[0][1]);
            return new PeriodInfos($month, $year);
        }
        throw new OscarException(sprintf("Format de période '%s' incorrect", $str));
    }

    public static function getPeriodInfos(string $str): array
    {
        return self::getPeriodInfosObj($str)->toArray();
    }
}