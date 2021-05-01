<?php
namespace webmaster\services\flow;

use common\models\offer\targets\wm\TargetWmView;
use Yii;
use common\models\flow\FlowTransit;
use common\models\flow\FlowLanding;
use common\models\flow\Flow;
use common\services\webmaster\Helper;
use yii\base\InvalidParamException;
use yii\db\Exception;

class FlowDataSave
{
    public $errors = [];

    /**
     * @param $request
     * @return array|int
     * @throws Exception
     * @throws \webmaster\services\flow\FlowNotFoundException
     */
    public function createFlow($request)
    {
        $flow = new Flow();
        $wm_id = Yii::$app->user->identity->getId();

        if (isset($request['wm_id']) && !Helper::checkWmAvailabilityTarget($request['wm_id'], $request['offer_id']))
            throw new FlowNotFoundException('There is no such purpose for this webmaster.');

        $flow->setAttributes([
            'wm_id' => isset($request['wm_id']) ? $request['wm_id'] : $wm_id,
            'offer_id' => $request['offer_id'],
            'advert_offer_target_status' => $request['advert_offer_target_status'],
            'flow_key' => Helper::generateFlowKey(),
            'flow_name' => $request['flow_name'],
            'use_tds' => $request['use_tds'],
            'active' => $request['active'],
        ]);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $flow->validate() ? $flow->save() : $this->errors['flow_landings'] = $flow->errors;
            if (isset($request['flow_landings']) && is_array($request['flow_landings'])) {
                foreach ($request['flow_landings'] as $landing) {
                    $landings = $this->saveLandings($flow->flow_id, $landing);
                    if ($landings != true || is_array($landings)) {
                        $this->errors['flow_landings'] = $landings;
                    }
                }
            }
            if (isset($request['flow_transits']) && is_array($request['flow_transits'])) {
                foreach ($request['flow_transits'] as $transit) {
                    $transits = $this->saveTransits($flow->flow_id, $transit);
                    if ($transits != true || is_array($transits)) {
                        $this->errors['flow_transits'] = $transits;
                    }
                }
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $transaction->rollBack();
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $transaction->rollBack();
        }

        return !empty($this->errors) ? $this->errors : $flow->flow_id;
    }

    /**
     * @param $flow_id
     * @param $landing_id
     * @return array|bool
     * @throws \webmaster\services\flow\FlowNotFoundException
     */
    public function saveLandings($flow_id, $landing_id)
    {
        if (!isset($flow_id)) {
            throw new FlowNotFoundException('The landing doesn\'t exist');
        }

        $flow_landing = new FlowLanding();

        $flow_landing->setAttributes([
            'flow_id' => $flow_id,
            'landing_id' => $landing_id,
        ]);

        return $flow_landing->validate() ? $flow_landing->save() : $flow_landing->errors;
    }

    /**
     * @param $flow_id
     * @param $transit_id
     * @return array|bool
     * @throws \webmaster\services\flow\FlowNotFoundException
     */
    public function saveTransits($flow_id, $transit_id)
    {
        if (!isset($flow_id)) {
            throw new FlowNotFoundException('The landing doesn\'t exist');
        }

        $flow_transit = new FlowTransit();

        $flow_transit->setAttributes([
            'flow_id' => $flow_id,
            'transit_id' => $transit_id,
        ]);

        return $flow_transit->validate() ? $flow_transit->save() : $flow_transit->errors;
    }

    /**
     * @param $request
     * @return array|bool
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function update($request)
    {
        $flow = Flow::findOne(['flow_id' => $request['flow_id']]);
        $sites = FlowLanding::findOne(['flow_id' => $request['flow_id']]);
        $wm_id = Yii::$app->user->identity->getId();

        if (!Helper::checkWmAvailabilityTarget($request['wm_id'], $request['offer_id']))
            throw new FlowNotFoundException('There is no such purpose for this webmaster.');

        $flow->setAttributes([
            'flow_name' => $request['flow_name'],
            'wm_id' => isset($request['wm_id']) ? $request['wm_id'] : $wm_id,
            'use_tds' => $request['use_tds'],
            'active' => $request['active'],
        ]);

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($sites) {
                $sites->delete();
            }

            $flow->validate() ? $flow->save() : $this->errors['flow_landings'] = $flow->errors;
            if (isset($request['flow_landings']) && is_array($request['flow_landings'])) {
                foreach ($request['flow_landings'] as $landing) {
                    $landings = $this->saveLandings($flow->flow_id, $landing);
                    if ($landings != true || is_array($landings)) {
                        $this->errors['flow_landings'] = $landings;
                    }
                }
            }
            if (isset($request['flow_transits']) && is_array($request['flow_transits'])) {
                foreach ($request['flow_transits'] as $transit) {
                    $transits = $this->saveTransits($flow->flow_id, $transit);
                    if ($transits != true || is_array($transits)) {
                        $this->errors['flow_transits'] = $transits;
                    }
                }
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $transaction->rollBack();
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $transaction->rollBack();
        }

        return !empty($this->errors) ? $this->errors : true;
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function delete($id)
    {
        $flow = Flow::findOne(['flow_id' => $id]);
        $flow->setAttribute('is_deleted', 1);

        return $flow->validate() ? $flow->save() : $flow->errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}