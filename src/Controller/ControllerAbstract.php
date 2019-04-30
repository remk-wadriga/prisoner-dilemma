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
use Mcfedr\JsonFormBundle\Exception\InvalidFormHttpException;
use Mcfedr\JsonFormBundle\Exception\InvalidJsonHttpException;
use Mcfedr\JsonFormBundle\Exception\MissingFormHttpException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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


    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param callable      $preValidation callback to be called before the form is validated
     *
     * @throws \Mcfedr\JsonFormBundle\Exception\InvalidFormHttpException
     * @throws \Mcfedr\JsonFormBundle\Exception\MissingFormHttpException
     * @throws \Mcfedr\JsonFormBundle\Exception\InvalidJsonHttpException
     */
    protected function handleJsonForm(FormInterface $form, Request $request, callable $preValidation = null)
    {
        $paramName = $form->getName();
        $data = $request->get($paramName);
        if (empty($data)) {
            $bodyJson = $request->getContent();
            if (!($body = json_decode($bodyJson, true))) {
                throw new InvalidJsonHttpException();
            }
            if (!isset($body[$paramName])) {
                throw new MissingFormHttpException($form);
            }
            $data = $body[$paramName];
        }

        $form->submit($data);

        if ($preValidation) {
            $preValidation();
        }

        if (!$form->isValid()) {
            throw new InvalidFormHttpException($form);
        }
    }
}