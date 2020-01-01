<?php

namespace Mild\Contract\Mail;

interface MessageInterface
{
    /**
     * @return array
     */
    public function getFrom();

    /**
     * @return array
     */
    public function getRecipients();

    /**
     * @param $email
     * @return void
     */
    public function addRecipient($email);

    /**
     * @param $email
     * @return void
     */
    public function setFrom($email);

    /**
     * @return string
     */
    public function toString();
}