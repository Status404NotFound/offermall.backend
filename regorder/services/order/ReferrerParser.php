<?php

namespace regorder\services\order;

use common\helpers\FishHelper;
use common\models\flow\Flow;

class ReferrerParser
{
    public $parts;
    public $referrer;
    public $flow;

    public function __construct($referrer)
    {
        $this->parts = parse_url($referrer);
        $this->handleReferrer();
    }

    public function handleReferrer()
    {
        $this->referrer['url'] = $this->parseReferrerUrl();
        $this->referrer['flow_key'] = $this->parseReferrerFlowKey();
        $this->referrer['params'] = isset($this->parts['query']) ? $this->parseUrlParams() : [];
        $this->flow = $this->getFlow();
    }


    private function parseReferrerUrl()
    {
        return isset($this->parts['host']) ?
            $this->parts['host'] . (isset($this->parts['path']) && $this->parts['path'] != '/' ?
                $this->parts['path'] : '') : null;
    }


    private function parseReferrerFlowKey()
    {
        $flow_key = isset($this->parts['path']) && $this->parts['path'] != '/' ? $this->parts['path'] : null;
        return trim(str_replace('/', '', $flow_key));
    }

    private function getFlow()
    {
        $flow = Flow::find()->where(['flow_key' => $this->referrer['flow_key']])->one();
        return $flow;
    }


    private function parseUrlParams()
    {
        // TODO: Parse params better (now only 1st and 2nd sub_ids)
        $this->parts = str_replace('subid', 'sub_id_', $this->parts['query']);
        return array_reduce(explode('&', $this->parts), function ($carry, $pairs) {
            $pairs = explode('=', $pairs);
            $carry[$pairs[0]] = $pairs[1];
            return $carry;
        }, []);
    }

    public function getReferrer()
    {
        return $this->referrer;
    }
}