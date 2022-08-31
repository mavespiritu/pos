<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Package;

/**
 * PackageSearch represents the model behind the search form of `common\modules\accounting\models\Package`.
 */
class PackageSearch extends Package
{
    public $branchName;
    public $programName;
    public $packageTypeName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id', 'package_type_id', 'tier'], 'integer'],
            [['code', 'branchName', 'programName', 'packageTypeName'], 'safe'],
            [['amount'], 'number'],
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
        $query = Package::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchName' => [
                    'asc' => ['accounting_branch.name' => SORT_ASC],
                    'desc' => ['accounting_branch.name' => SORT_DESC],
                ],
                'programName' => [
                    'asc' => ['accounting_program.name' => SORT_ASC],
                    'desc' => ['accounting_program.name' => SORT_DESC],
                ],
                'packageTypeName' => [
                    'asc' => ['accounting_package_type.name' => SORT_ASC],
                    'desc' => ['accounting_package_type.name' => SORT_DESC],
                ],
                'tier',
                'code',
                'amount'
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
            'branch_id' => $this->branch_id,
            'package_type_id' => $this->package_type_id,
            'tier' => $this->tier,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
              ->andFilterWhere(['like', 'accounting_branch.name', $this->branchName])
              ->andFilterWhere(['like', 'accounting_package_type.name', $this->packageTypeName]);

        if($params){
            $query->joinWith(['branch' => function ($q) {
                if($this->branchName!=""){
                $q->where('accounting_branch.name LIKE "%' . $this->branchName . '%"');
                }
            }]);

            $query->joinWith(['program' => function ($q) {
                if($this->programName!=""){
                $q->where('accounting_program.name LIKE "%' . $this->programName . '%"');
                }
            }]);

            $query->joinWith(['packageType' => function ($q) {
                if($this->packageTypeName!=""){
                $q->where('accounting_package_type.name LIKE "%' . $this->packageTypeName . '%"');
                }
            }]);
        }

        return $dataProvider;
    }
}
