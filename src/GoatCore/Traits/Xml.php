<?php
namespace GoatCore\Traits;

use \SimpleXMLElement;

trait Xml
{
    /**
    * Convert array to XML
    * @param array $data
    * @return string $xml
    */
    public function arrayToXml($data, $xml = false, $prefix = '')
    {
        if ($xml === false) {

            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><api-response/>');
            $data = $this->toArray($data);
        }

        foreach ($data as $key => $value) {

            if (is_object($value)) {

                $value = json_decode(json_encode($value), true);
            }

            $keyAdd = is_numeric($key) ? "{$prefix}-{$key}": $key;

            if (is_array($value)) {

                $this->arrayToXml($value, $xml->addChild($keyAdd), $key);
            }else{

                $xml->addChild($keyAdd, htmlspecialchars($value, ENT_QUOTES, "UTF-8"));
            }
        }

        return $xml->asXML();
    }


    protected function toArray($data) {
        if (is_object($data) || is_scalar($data)) {

            return ['data' => $data];
        }

        if (is_resource($data)) {

            return ['Resource usage is not allowed'];
        }

        return $data;
    }
}
