<?php

/**
 *  Heartland Abstract PayPlan Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

abstract class AbstractPayPlanRequest extends AbstractRequest
{
    // region Heartland Request Building

    public function getData()
    {
        //check whether secretApiKey or siteid details passed
        if ($this->getSecretApiKey() == null) {
            $this->validate(
                'siteId',
                'deviceId',
                'licenseId',
                'username',
                'password',
                'serviceUri'
            );
        } else {
            $this->validate('secretApiKey');
        }

        // TODO: Build correct validations based on request type.
    }

    public function sendData($data)
    {
        $http = isset($data['http']) ? $data['http'] : [];
        $uri = isset($http['uri']) ? $http['uri'] : '';

        if (isset($data['limit']) && isset($data['offset'])) {
            $paging = array(
                'limit'  => $data['limit'],
                'offset' => $data['offset'],
            );
            $uri .= '?' . http_build_query($paging);
        }

        $identity = array();
        if ($this->getSiteId() !== null) {
            $identity[0] = 'SiteID=' . $this->getSiteId();
        }
        if ($this->getDeviceId() !== null) {
            $identity[1] = 'DeviceID=' . $this->getDeviceId();
        }
        if ($this->getLicenseId() !== null) {
            $identity[2] = 'LicenseID=' . $this->getLicenseId();
        }

        $auth = $this->getUsername() !== null
            ? $this->getUsername() . ':' . $this->getPassword()
            : $this->getSecretApiKey();

        $fieldsToIgnore = [
          'http',
          'limit',
          'offset',
        ];

        $data = array_filter($data, function ($k) use ($fieldsToIgnore) {
            return !in_array($k, $fieldsToIgnore);
        }, ARRAY_FILTER_USE_KEY);

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($auth),
            'Content-type' => 'application/json; charset="UTF-8"',
        ];

        if ($this->getUsername() !== null) {
            $headers['HPS-Identity'] = implode(',', $identity);
        }

        return $this->submitRequest([
            'body' => json_encode($data),
            'headers' => $headers,
            'http' => [
              'uri' => $uri,
              'verb' => isset($http['verb']) ? $http['verb'] : 'GET',
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->getSecretApiKey() != null && $this->getSecretApiKey() != "") {
            if (strpos($this->getSecretApiKey(), '_cert_') !== false) {
                $this->setParameter('testMode', true);
                return "https://cert.api2.heartlandportico.com/Portico.PayPlan.v2";
            } elseif (strpos($this->getSecretApiKey(), '_uat_') !== false) {
                $this->setParameter('testMode', true);
                return "https://api.heartlandportico.com/payplan.v2";
            } else {
                $this->setParameter('testMode', false);
                return "https://api-uat.heartlandportico.com/payplan.v2";
            }
        } else {
            return $this->getServiceUri();
        }
    }

    // endregion
}
