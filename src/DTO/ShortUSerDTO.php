<?php

namespace App\DTO;
use JMS\Serializer\Annotation as Serializer;

class ShortUSerDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $username;


    /**
     * @Serializer\Type("string")
     */
    public $password;
}