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
            case '-2':
                $e = static::formResponseException(
                    'Authentication Error. Please double check your service configuration',
                    HpsExceptionCodes::AUTHENTICATION_ERROR
                );
                break;
            case '3':
                $e = static::formResponseException(
                    $responseText,
                    HpsExceptionCodes::INVALID_ORIGINAL_TRANSACTION
                );
                break;
            case '5':
                $e = static::formResponseException(
                    $responseText,
                    HpsExceptionCodes::NO_OPEN_BATCH
                );
                break;
            case '12':
                $e = static::formResponseException(
                    'Invalid CPC data',
                    HpsExceptionCodes::INVALID_CPC_DATA
                );
                break;
            case '27':
            case '34':
            case '26':
            case '13':
                $e = static::formResponseException(
                    'Invalid card data',
                    HpsExceptionCodes::INVALID_CARD_DATA
                );
                break;
            case '14':
                $e = static::formResponseException(
                    'The card number is not valid',
                    HpsExceptionCodes::INVALID_NUMBER
                );
                break;
            case '30':
                $e = static::formResponseException(
                    'Gateway timed out',
                    HpsExceptionCodes::GATEWAY_ERROR
                );
                break;
            case '1':
            default:
                $e = static::formResponseException(
                    $responseText,
                    HpsExceptionCodes::UNKNOWN_GATEWAY_ERROR
                );
        }

        return $e;
    }

    public static function formResponseException($responseException, $exceptionCode = '')
    {
        $exception = new \stdClass();
        $exception->message = $responseException;
        $exception->code = $exceptionCode;
        return $exception;
    }
}
