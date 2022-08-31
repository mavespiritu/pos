<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosEnrolment;

/**
 * PosEnrolmentSearch represents the model behind the search form of `common\modules\pos\models\PosEnrolment`.
 */
class PosEnrolmentSearch extends PosEnrolment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'customer_id', 'product_id', 'enrolment_type_id'], 'integer'],
            [['enrolment_date', 'datetime'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? 
        PosEnrolment::find()
        ->joinWith('customer')
        ->joinWith('product')
        ->joinWith('enrolmentType')
        ->joinWith('season.branchProgram.branch')
        ->joinWith('season.branchProgram.program')
        ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
        ->orderBy(['pos_enrolment.id' => SORT_DESC]) : 
        PosEnrolment::find()
        ->orderBy(['pos_enrolment.id' => SORT_DESC]);


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'pos_enrolment.id' => $this->id,
            'pos_enrolment.season_id' => $this->season_id,
            'pos_enrolment.customer_id' => $this->customer_id,
            'pos_enrolment.product_id' => $this->product_id,
            'pos_enrolment.enrolment_type_id' => $this->enrolment_type_id,
            'pos_enrolment.enrolment_date' => $this->enrolment_date,
            'pos_enrolment.datetime' => $this->datetime,
        ]);

        return $dataProvider;
    }
}
