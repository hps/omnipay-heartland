<?php

namespace Omnipay\Heartland\Message;

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
            return $e;
        }
        if (!isset($response->Transaction) || !isset($response->Transaction->$expectedType)) {
            return 'Unexpected response from HPS gateway: '. HpsExceptionCodes::UNEXPECTED_GATEWAY_ERROR;
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
            $e = self::formResponseException(
                'Authentication Error. Please double check your service configuration', HpsExceptionCodes::AUTHENTICATION_ERROR
            );
            break;
        case '3':
            $e = self::formResponseException(
                $responseText, HpsExceptionCodes::INVALID_ORIGINAL_TRANSACTION
            );
            break;
        case '5':
            $e = self::formResponseException(
                $responseText, HpsExceptionCodes::NO_OPEN_BATCH
            );
            break;
        case '12':
            $e = self::formResponseException(
                'Invalid CPC data', HpsExceptionCodes::INVALID_CPC_DATA
            );
            break;
        case '27':
        case '34':
        case '26':
        case '13':
            $e = self::formResponseException(
                'Invalid card data', HpsExceptionCodes::INVALID_CARD_DATA
            );
            break;
        case '14':
            $e = self::formResponseException(
                'The card number is not valid', HpsExceptionCodes::INVALID_NUMBER
            );
            break;
        case '30':
            $e = self::formResponseException(
                'Gateway timed out', HpsExceptionCodes::GATEWAY_ERROR
            );
            break;
        case '1':
        default:
            $e = self::formResponseException(
                $responseText, HpsExceptionCodes::UNKNOWN_GATEWAY_ERROR
            );
        }

        return $e;
    }
    
    public static function formResponseException($responseException, $exceptionCode = '')
    {
        return $responseException;
    }

}
