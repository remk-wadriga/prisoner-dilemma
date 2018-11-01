<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 22.10.2018
 * Time: 13:15
 */

namespace App\Controller;

use App\Entity\User;
use Mcfedr\JsonFormBundle\Controller\JsonController;

abstract class ControllerAbstract extends JsonController
{
    public function getUser(): User
    {
        return parent::getUser();
    }

    public function formatDateTime(?\DateTimeInterface $date, string $format = null): string
    {
        if ($format === null && $this->container->hasParameter('frontend_date_time_format')) {
            $format = $this->container->getParameter('frontend_date_time_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        return $date->format($format);
    }

    public function formatDate(?\DateTimeInterface $date, string $format = null): string
    {
        if ($format === null && $this->container->hasParameter('frontend_date_format')) {
            $format = $this->container->getParameter('frontend_date_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        return $date->format($format);
    }

    public function formatTime(?\DateTimeInterface $date, string $format = null): string
    {
        if ($format === null && $this->container->hasParameter('frontend_time_format')) {
            $format = $this->container->getParameter('frontend_time_format');
        }
        if ($date === null || $format === null) {
            return '';
        }
        return $date->format($format);
    }
}