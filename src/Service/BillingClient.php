<?php

namespace App\Service;


use App\DTO\Converter;
use App\DTO\UserDTO;
use App\Security\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use App\Exception\BillingUnavailableException;

class BillingClient
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function userLogin($credentials): ?User
    {

        $qm = new ApiManager(
            '/api/v1/auth',
            'POST',
            [
                'Content-Type: application/json',
            ],
            $credentials
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true, 512, JSON_PARTIAL_OUTPUT_ON_ERROR);
        if (!(isset($arrayResponse['token']))) {
            return null;
        }
        if (!(isset($arrayResponse['code']))) {
            $userDTO = $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
            return (new Converter())->fromDTO($userDTO);
        }
        if ($arrayResponse['code'] === Response::HTTP_UNAUTHORIZED) {
            throw new UserNotFoundException('Неверные учетные данные');
        }
    }

    public function userRegister($credentialsObject): User
    {
        $credentials = $this->serializer->serialize($credentialsObject, 'json');
        $qm = new ApiManager(
            '/api/v1/register',
            'POST',
            [
                'Content-Type: application/json',
            ],
            $credentials
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true, 512, JSON_PARTIAL_OUTPUT_ON_ERROR);
        if (isset($arrayResponse['error'])) {
            throw new BillingUnavailableException($arrayResponse['error']);
        }
        $userDTO = $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
        return (new Converter())->fromDTO($userDTO);
    }

    public function getCurrentUser(User $user)
    {
        $qm = new ApiManager(
            '/api/v1/current',
            'GET',
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $user->getApiToken()
            ]
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true);
        if (isset($arrayResponse['error'])) {
            throw new BillingUnavailableException($arrayResponse['error']);
        }
        return $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
    }
}