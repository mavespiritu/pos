<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosCustomer;

/**
 * PosCustomerSearch represents the model behind the search form of `common\modules\pos\models\PosCustomer`.
 */
class PosCustomerSearch extends PosCustomer
{
    public $fullName;
    public $schoolName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'school_id', 'year_graduated'], 'integer'],
            [['id_number', 'province_id', 'citymun_id', 'first_name', 'middle_name', 'last_name', 'ext_name', 'address', 'contact_no', 'birthday', 'prc', 'email_address', 'fullName', 'schoolName'], 'safe'],
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
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosCustomer::find()
        ->joinWith('school')
        ->andWhere(['pos_school.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]) :
         PosCustomer::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'fullName' => [
                    'asc' => ['concat(first_name," ",middle_name," ",last_name," ",ext_name)' => SORT_ASC],
                    'desc' => ['concat(first_name," ",middle_name," ",last_name," ",ext_name)' => SORT_DESC],
                ],
                'schoolName' => [
                    'asc' => ['pos_school.title' => SORT_ASC],
                    'desc' => ['pos_school.title' => SORT_DESC],
                ],
                'contact_no',
                'email_address',
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'school_id' => $this->school_id,
            'year_graduated' => $this->year_graduated,
            'birthday' => $this->birthday,
        ]);

        $query->andFilterWhere(['like', 'id_number', $this->id_number])
            ->andFilterWhere(['like', 'province_id', $this->province_id])
            ->andFilterWhere(['like', 'citymun_id', $this->citymun_id])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'contact_no', $this->contact_no])
            ->andFilterWhere(['like', 'prc', $this->prc])
            ->andFilterWhere(['like', 'email_address', $this->email_address])
            ->andFilterWhere(['like', 'concat(first_name," ",middle_name," ",last_name," ",ext_name)', $this->fullName])
            ->andFilterWhere(['like', 'pos_school.title', $this->schoolName]);

        return $dataProvider;
    }
}
