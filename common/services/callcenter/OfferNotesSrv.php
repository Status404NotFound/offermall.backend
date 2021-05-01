<?php
namespace common\services\callcenter;


use common\models\DataList;
use common\models\offer\Offer;
use common\models\offer\OfferNote;
use Yii;
use yii\helpers\ArrayHelper;

class OfferNotesSrv
{
    public $owner;

    public function __construct()
    {
        $dataList = new DataList();
        $this->owner = is_array($dataList->owner) ? $dataList->owner[0] : $dataList->owner;
    }

    public function get()
    {
        $dataList = new DataList();

        $available_offers = ArrayHelper::getColumn($dataList->getOffers(), 'offer_id');

        $offers = Offer::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
            ])
            ->where(['offer.offer_id' => $available_offers])
            ->andWhere(['offer.offer_status' => Offer::STATUS_ACTIVE])
            ->asArray()
            ->groupBy('offer.offer_id')
            ->all();

        foreach ($offers as $key => $offer)
        {
//            $goes = ArrayHelper::getColumn($dataList->getOfferGeo($offer['offer_id']), 'country_id');
            $goes = $dataList->getOfferGeo($offer['offer_id']);
            $offers[$key]['geo'] = $this->getOfferGeosNotes($goes, $offer['offer_id']);
        }

        return $offers;
    }


    public function save($offer_id, $notes)
    {
        foreach ($notes as $note){

            $offerNote = OfferNote::find()->where(['offer_id' => $offer_id, 'advert_id'=>$this->owner, 'geo_id' => $note['country_id']])->one();
            if (!$offerNote) {
                $offerNote = new OfferNote();
                $offerNote->geo_id = $note['country_id'];
                $offerNote->offer_id = $offer_id;
                $offerNote->advert_id = $this->owner;
            }

            $offerNote->note = $note['note'];
            $offerNote->save();
        }

        return true;
    }


    public function getByOfferGeo($offer_id, $geo_id)
    {
        $offerNote = OfferNote::find()->where(['offer_id' => $offer_id, 'advert_id'=>$this->owner, 'geo_id' => $geo_id])->one();
        if ($offerNote){
            return $offerNote->note;
        }

        return null;
    }

    private function getOfferGeosNotes($geos, $offer_id)
    {
        foreach ($geos as $key => $geo)
        {
//            var_dump($geo);exit;
            $geos[$key]['note'] = $this->getByOfferGeo($offer_id, $geo['country_id']);
        }


//        $notes = OfferNote::find()
//            ->select([
//                'countries.id as geo_id',
//                'countries.country_name',
//                'countries.country_code as iso',
//                'note',
//            ])
//            ->join('RIGHT JOIN', 'countries', 'offer_note.geo_id = countries.id')
//            ->join('RIGHT JOIN', 'offer', 'offer.offer_id=offer_note.offer_id')
//            ->where(['countries.id' => $geos])
//            ->andWhere(['offer.offer_id' => $offer_id])
//            ->groupBy('countries.id')
//            ->asArray()
//            ->all();

        return $geos;
    }
}