<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 02.05.2019
 * Time: 22:12
 */

namespace App\Service;


class FormatterService extends AbstractService
{
    public function toFloat($val, $decimals = 2 , $decPoint = '.' , $thousandsSep = '')
    {
        return floatval(number_format($val, $decimals, $decPoint, $thousandsSep));
    }

    public function toInt($val, $thousandsSep = '')
    {
        return intval(number_format($val, 0, '.', $thousandsSep));
    }

    public function toDateTime($date = null, bool $toFrontend = true, string $format = null): string
    {
        if ($format === null) {
            $format = $toFrontend ? $this->getParam('frontend_date_time_format') : $this->getParam('backend_date_time_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }

    public function toDate($date = null, bool $toFrontend = true, string $format = null): string
    {
        if ($format === null) {
            $format = $toFrontend ? $this->getParam('frontend_date_format') : $this->getParam('backend_date_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }

    public function toTime($date = null, bool $toFrontend = true, string $format = null): string
    {
        if ($format === null) {
            $format = $toFrontend ? $this->getParam('frontend_time_format') : $this->getParam('backend_time_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }
}