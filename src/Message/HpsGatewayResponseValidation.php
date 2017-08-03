<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Class HpsGatewayResponseValidation
 */
class HpsGatewayResponseValidation
{

    /**
     * @param $response
     * @param $expectedType
     *
     * @throws \HpsAuthenticationException
     * @throws \HpsGatewayException
     * @throws null
     */
    public static function checkResponse($response, $expectedType) 
    {
        $rspCode = $response->Header->GatewayRspCode;
        $rspText = $response->Header->GatewayRspMsg;
        $e = HpsGatewayResponseValidation::getException($rspCode, $rspText, $response);

        if ($e != null) {
            throw $e;
        }
        if (!isset($response->Transaction) || !isset($response->Transaction->$expectedType)) {
            throw new InvalidResponseException(
                'Unexpected response from HPS gateway', HpsExceptionCodes::UNEXPECTED_GATEWAY_ERROR
            );
        }
    }

    /**
     * @param $responseCode
     * @param $responseText
     * @param $response
     *
     * @return \HpsAuthenticationException|\HpsGatewayException|null
     */
    public static function getException($responseCode, $responseText, $response) 
    {
        $e = null;

        switch ($responseCode) {
        case '0':
            break;
        case '-2':
            $e = new InvalidResponseException(
                'Authentication Error. Please double check your service configuration', HpsExceptionCodes::AUTHENTICATION_ERROR
            );
            break;
        case '3':
            $e = new InvalidResponseException(
                $responseText, HpsExceptionCodes::INVALID_ORIGINAL_TRANSACTION
            );
            break;
        case '5':
            $e = new InvalidResponseException(
                $responseText, HpsExceptionCodes::NO_OPEN_BATCH
            );
            break;
        case '12':
            $e = new InvalidResponseException(
                'Invalid CPC data', HpsExceptionCodes::INVALID_CPC_DATA
            );
            break;
        case '27':
        case '34':
        case '26':
        case '13':
            $e = new InvalidResponseException(
                'Invalid card data', HpsExceptionCodes::INVALID_CARD_DATA
            );
            break;
        case '14':
            $e = new InvalidResponseException(
                'The card number is not valid', HpsExceptionCodes::INVALID_NUMBER
            );
            break;
        case '30':
            $e = new InvalidResponseException(
                'Gateway timed out', HpsExceptionCodes::GATEWAY_ERROR
            );
            break;
        case '1':
        default:
            $e = new InvalidResponseException(
                $responseText, HpsExceptionCodes::UNKNOWN_GATEWAY_ERROR
            );
        }

        return $e;
    }

}
