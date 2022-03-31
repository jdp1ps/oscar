<?php

namespace Oscar\Utils;

use Oscar\Exception\OscarException;

class PeriodInfos
{
    private int $month;
    private int $year;

    private string $_label;
    private string $_code;
    private ?int $_totalDays = null;
    private ?\DateTime $_start = null;
    private ?\DateTime $_end = null;

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
    public function prevMonth(): self
    {
        $this->month--;
        if ($this->month == 0) {
            $this->month = 12;
            $this->year--;
        }
        $this->resetCache();
        return $this;
    }

    /**
     * Incremente d'un mois.
     *
     * @return $this
     */
    public function nextMonth(): self
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
        $this->resetCache();
        return $this;
    }

    /**
     * Reset cached datas
     */
    protected function resetCache(): void
    {
        $this->_totalDays = null;
        $this->_start = null;
        $this->_end = null;
        $this->_label = '';
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @return string
     * @throws \Exception
     */
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

    /**
     * Retourne le code de la période sous la forme YYYY-MM
     *
     * @return string
     */
    public function getPeriodCode(): string
    {
        return sprintf('%s-%s', $this->getYear(), ($this->getMonth() < 10 ? '0' : '') . $this->getMonth());
    }

    public function getPeriodSimple(): string
    {
        return sprintf('%s-%s', $this->getYear(), $this->getMonth());
    }

    public function toArray(): array
    {
        return [
            'period' => sprintf('%s-%s', $this->getYear(), $this->getMonth()),
            'periodLabel' => $this->getPeriodLabel(),
            'periodCode' => $this->getPeriodCode(),
            'periodCodeSimple' => $this->getPeriodCode(),
            'month' => $this->getMonth(),
            'year' => $this->getYear()
        ];
    }

    /**
     * Retourne le premier jour de la période.
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getStart(): \DateTime
    {
        if ($this->_start == null) {
            $this->_start = new \DateTime(sprintf('%s-01 00:00:00', $this->getPeriodCode()));
        }
        return $this->_start;
    }

    /**
     * Retourne le dernier jour de la période.
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getEnd(): \DateTime
    {
        if ($this->_end == null) {
            $this->_end = new \DateTime(sprintf('%s-%s 23:59:59', $this->getPeriodCode(), $this->getTotalDays()));
        }
        return $this->_end;
    }

    public function getTotalDays(): int
    {
        if (!$this->_totalDays) {
            $this->_totalDays = cal_days_in_month(CAL_GREGORIAN, $this->getMonth(), $this->getYear());
        }
        return $this->_totalDays;
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