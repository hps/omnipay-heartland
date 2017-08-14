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
    protected $responseType = '\Omnipay\Heartland\Message\PayPlanResponse';

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
        $http = isset($data['http']) ? $data['http'] : array();
        $uri = isset($http['uri']) ? '/' . trim($http['uri'], '/') : '';

        if (isset($data['limit']) && isset($data['offset'])) {
            $paging = array(
                'limit'  => $data['limit'],
                'offset' => $data['offset'],
            );
            $uri .= '?' . http_build_query($paging);
        }

        $identity = array();
        if ($this->getSiteId() !== null && $this->getSiteId() !== '') {
            $identity[0] = 'SiteID=' . $this->getSiteId();
        }
        if ($this->getDeviceId() !== null && $this->getDeviceId() !== '') {
            $identity[1] = 'DeviceID=' . $this->getDeviceId();
        }
        if ($this->getLicenseId() !== null && $this->getLicenseId() !== '') {
            $identity[2] = 'LicenseID=' . $this->getLicenseId();
        }

        $auth = $this->getUsername() !== null && $this->getUsername() !== ''
            ? $this->getUsername() . ':' . $this->getPassword()
            : $this->getSecretApiKey();

        $fieldsToIgnore = array_merge(array(
          'http',
          'limit',
          'offset',
        ), array_keys((new \Omnipay\Heartland\Gateway())->getDefaultParameters()));

        $allowedFields = array_filter(array_keys($data), function ($key) use ($fieldsToIgnore) {
            return !in_array($key, $fieldsToIgnore);
        });

        $data = array_intersect_key($data, array_flip($allowedFields));

        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($auth),
            'Content-Type' => 'application/json; charset=utf-8',
        );

        if ($this->getUsername() !== null && $this->getSiteId() !== '') {
            $headers['HPS-Identity'] = implode(',', $identity);
        }

        return $this->submitRequest(array(
            'body' => json_encode($data === array() ? (object) array() : $data),
            'headers' => $headers,
            'http' => array(
              'uri' => $uri,
              'verb' => isset($http['verb']) ? $http['verb'] : 'GET',
            ),
        ));
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
